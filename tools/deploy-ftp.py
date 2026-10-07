#!/usr/bin/env python3
"""Development helper: deploy the contents of htdocs/ to the web host via FTPS.

Not needed on the server. Python 3.8+, standard library only.

    python3 tools/deploy-ftp.py list                # recursive listing of the server
    python3 tools/deploy-ftp.py diff                # compare server with htdocs/ (read-only)
    python3 tools/deploy-ftp.py deploy --dry-run    # show what would be uploaded
    python3 tools/deploy-ftp.py deploy              # upload what differs, verify sizes

Credentials come from the environment: REICHI_FTP_HOST, REICHI_FTP_USER, REICHI_FTP_PASS
(the Cloud Agent secrets of the same name). The FTP root must be the web root.

Rules, matching the installation chapter of README.md:
  - the server is listed and compared first; `diff` and `--dry-run` never write;
  - files with the same size are compared by SHA-256 (downloaded), so a same-size edit is
    still detected and an unchanged file is never re-uploaded;
  - app/config.php, storage/secret.php and the runtime data in storage/ratelimit/ and
    storage/logs/ are never written, whatever the local tree contains;
  - nothing is ever deleted - files that exist only on the server are reported;
  - each upload goes to <name>.uploading~ first and is renamed into place.

Host quirks handled here (World4You, Pure-FTPd):
  - TLS 1.3 data connections are reset by the server, so TLS is capped at 1.2;
  - the server refuses passive data connections whose source IP differs from the control
    connection (anti-FXP), closes them and keeps waiting. From a network that leaves through
    a NAT pool (several public IPs chosen per TCP connection, e.g. a cloud VM) stock clients
    therefore hang and leave sessions open until the "8 connections as the same user" limit
    hits. This client reconnects to the same passive port until the server accepts the data
    connection (signalled by the 150 reply on the control channel).
"""
import argparse
import hashlib
import json
import os
import select
import socket
import ssl
import sys
import time
from ftplib import FTP_TLS, error_perm, error_reply, error_temp

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
LOCAL = os.path.join(ROOT, "htdocs")

# Never written by this script.
PROTECTED = {"/app/config.php", "/storage/secret.php"}
PROTECTED_PREFIX = ("/storage/ratelimit/", "/storage/logs/")

MAX_DATA_TRIES = 400


class NatPoolFTPS(FTP_TLS):
    def __init__(self, host, user, password, timeout=60, verbose=False):
        ctx = ssl.create_default_context()
        ctx.maximum_version = ssl.TLSVersion.TLSv1_2
        super().__init__(context=ctx, timeout=timeout)
        self.verbose = verbose
        self.data_tries = 0
        self.transfers = 0
        self.connect(host, 21)
        self.auth()
        self.login(user, password)
        self.prot_p()
        self.set_pasv(True)

    def ntransfercmd(self, cmd, rest=None):
        host, port = self.makepasv()
        if rest is not None:
            self.sendcmd("REST %s" % rest)
        # Send the command first so the server enters its accept loop.
        self.putcmd(cmd)
        ctrl = self.sock
        tries = 0
        while True:
            tries += 1
            self.data_tries += 1
            if tries > MAX_DATA_TRIES:
                raise error_temp("data connection never accepted after %d tries" % MAX_DATA_TRIES)
            conn = socket.create_connection((host, port), self.timeout, source_address=self.source_address)
            # Whoever speaks first decides: control -> 150 (accepted); data -> EOF (rejected).
            readable, _, _ = select.select([ctrl, conn], [], [], self.timeout)
            if not readable:
                conn.close()
                raise error_temp("data connection: no reaction from server")
            if conn in readable and ctrl not in readable:
                try:
                    peek = conn.recv(1, socket.MSG_PEEK)
                except OSError:
                    peek = b""
                if peek == b"":
                    conn.close()
                    time.sleep(0.05)
                    continue
            resp = self.getresp()
            if resp[0] == "2":
                resp = self.getresp()
            if resp[0] != "1":
                conn.close()
                raise error_reply(resp)
            break
        if self.verbose and tries > 1:
            print(f"    data connection accepted after {tries} tries", file=sys.stderr)
        self.transfers += 1
        if self._prot_p:
            conn = self.context.wrap_socket(conn, server_hostname=self.host, session=self.sock.session)
        return conn, None

    def listdir(self, path):
        out = []
        for name, facts in self.mlsd(path):
            if name in (".", ".."):
                continue
            size = int(facts.get("size", facts.get("sizd", 0)) or 0)
            out.append((name, facts.get("type"), size, facts.get("modify"), facts.get("unix.mode")))
        return sorted(out, key=lambda e: (e[1] != "dir", e[0].lower()))

    def walk(self, path="/"):
        for name, kind, size, modified, mode in self.listdir(path):
            full = path.rstrip("/") + "/" + name
            yield full, kind, size, modified, mode
            if kind == "dir":
                yield from self.walk(full)

    def download_bytes(self, path):
        buf = bytearray()
        self.retrbinary("RETR " + path, buf.extend)
        return bytes(buf)

    def upload_file(self, local, remote):
        tmp = remote + ".uploading~"
        with open(local, "rb") as fh:
            self.storbinary("STOR " + tmp, fh)
        try:
            self.rename(tmp, remote)
        except error_perm:
            self.delete(remote)
            self.rename(tmp, remote)
        return os.path.getsize(local)

    def quit_quiet(self):
        try:
            self.quit()
        except Exception:
            self.close()


def connect(verbose=False):
    try:
        host, user, password = (os.environ["REICHI_FTP_" + k] for k in ("HOST", "USER", "PASS"))
    except KeyError as e:
        sys.exit(f"missing environment variable {e}")
    ftp = NatPoolFTPS(host, user, password, verbose=verbose)
    print(f"# connected to {host} (FTPS, TLS 1.2), root = {ftp.pwd()}")
    return ftp


def is_protected(rel):
    if rel in PROTECTED:
        return True
    return rel.startswith(PROTECTED_PREFIX) and not rel.endswith(".gitkeep")


def local_tree():
    files = {}
    for root, _, names in os.walk(LOCAL):
        for name in names:
            path = os.path.join(root, name)
            files["/" + os.path.relpath(path, LOCAL).replace(os.sep, "/")] = path
    return files


def server_tree(ftp):
    files, dirs = {}, set()
    for full, kind, size, _, _ in ftp.walk("/"):
        if kind == "dir":
            dirs.add(full)
        else:
            files[full] = size
    return files, dirs


def sha256(data):
    return hashlib.sha256(data).hexdigest()


def compare(ftp, local, server, cache):
    """-> (to_upload [(rel, reason)], identical [rel], server_only [rel])"""
    to_upload, identical = [], []
    for rel in sorted(local):
        if is_protected(rel):
            continue
        if rel not in server:
            to_upload.append((rel, "new"))
        elif server[rel] != os.path.getsize(local[rel]):
            to_upload.append((rel, "size"))
        else:
            if cache.get(rel, {}).get("size") != server[rel]:
                cache[rel] = {"size": server[rel], "sha256": sha256(ftp.download_bytes(rel))}
            with open(local[rel], "rb") as fh:
                if cache[rel]["sha256"] == sha256(fh.read()):
                    identical.append(rel)
                else:
                    to_upload.append((rel, "content"))
        done = len(to_upload) + len(identical)
        if done % 25 == 0:
            print(f"  ... compared {done}/{len(local)}", file=sys.stderr)
    server_only = sorted(p for p in server if p not in local)
    return to_upload, identical, server_only


def cmd_list(args):
    ftp = connect(args.verbose)
    n = 0
    for full, kind, size, modified, mode in ftp.walk("/"):
        n += 1
        if kind == "dir":
            print(f"D {mode or '-':>6} {'':>9} {modified or '-'} {full}/")
        else:
            print(f"F {mode or '-':>6} {size:>9} {modified or '-'} {full}")
    ftp.quit_quiet()
    print(f"# {n} entries, {ftp.transfers} transfers, {ftp.data_tries} data connects", file=sys.stderr)


def report(to_upload, identical, server_only, local, server):
    print(f"\n# identical (verified by SHA-256 or protected): {len(identical)}")
    print(f"# to upload: {len(to_upload)}")
    for rel, why in to_upload:
        extra = "" if why == "new" else f"  (server {server[rel]} B, local {os.path.getsize(local[rel])} B)"
        print(f"   {why:7} {rel}{extra}")
    print(f"# only on the server (never deleted by this script): {len(server_only)}")
    for rel in server_only:
        print(f"   -       {rel}  {server[rel]} B")


def cmd_diff(args, do_upload=False):
    ftp = connect(args.verbose)
    local = local_tree()
    server, dirs = server_tree(ftp)
    print(f"# local files: {len(local)}, server files: {len(server)}")
    cache_file = os.path.join(ROOT, ".deploy-ftp-cache.json")
    cache = {}
    if os.path.exists(cache_file):
        with open(cache_file) as fh:
            cache = json.load(fh)
    try:
        to_upload, identical, server_only = compare(ftp, local, server, cache)
    finally:
        with open(cache_file, "w") as fh:
            json.dump(cache, fh)
    report(to_upload, identical, server_only, local, server)

    if not do_upload or args.dry_run:
        ftp.quit_quiet()
        print("\n# nothing uploaded" + (" (dry run)" if do_upload else ""))
        return

    # Static files first, PHP last, so a half-finished run never references missing assets.
    uploaded = []
    for rel, why in sorted(to_upload, key=lambda x: (x[0].endswith(".php"), x[0])):
        parent, parts = "", os.path.dirname(rel).strip("/").split("/")
        for part in [p for p in parts if p]:
            parent += "/" + part
            if parent not in dirs:
                ftp.mkd(parent)
                dirs.add(parent)
                print(f"  mkdir    {parent}")
        size = ftp.upload_file(local[rel], rel)
        cache.pop(rel, None)
        uploaded.append((rel, size))
        print(f"  uploaded {rel} ({size} B, {why})")
    with open(cache_file, "w") as fh:
        json.dump(cache, fh)

    print("\n# verification")
    ok = True
    for directory in sorted({os.path.dirname(r) or "/" for r, _ in uploaded}):
        names = {n: s for n, kind, s, _, _ in ftp.listdir(directory) if kind == "file"}
        for rel, size in uploaded:
            if os.path.dirname(rel) == directory:
                got = names.get(os.path.basename(rel))
                ok &= got == size
                print(f"  {'ok' if got == size else f'MISMATCH (server {got} B)':>10}  {rel}")
        leftovers = [n for n in names if n.endswith(".uploading~")]
        if leftovers:
            ok = False
            print(f"  temp files left in {directory}: {leftovers}")
    ftp.quit_quiet()
    print(f"\n# {len(uploaded)} files uploaded, {ftp.transfers} transfers, {ftp.data_tries} data connects, "
          f"all verified: {ok}")
    sys.exit(0 if ok else 1)


def main():
    parser = argparse.ArgumentParser(description=__doc__.split("\n\n")[0])
    parser.add_argument("-v", "--verbose", action="store_true", help="report data-connection retries")
    sub = parser.add_subparsers(dest="command", required=True)
    sub.add_parser("list", help="recursive listing of the server")
    sub.add_parser("diff", help="compare server with htdocs/ (read-only)")
    deploy = sub.add_parser("deploy", help="upload what differs")
    deploy.add_argument("--dry-run", action="store_true", help="compare only, upload nothing")
    args = parser.parse_args()
    if args.command == "list":
        cmd_list(args)
    elif args.command == "diff":
        args.dry_run = True
        cmd_diff(args)
    else:
        cmd_diff(args, do_upload=True)


if __name__ == "__main__":
    main()
