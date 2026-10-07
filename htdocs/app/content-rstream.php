<?php
/**
 * rstream.at – Inhalte der Streaming-Abteilung (Livestream-Produktion)
 *
 * Gleiche Konventionen wie content.php und content-it.php: nur hier werden Texte und Links
 * gepflegt, die Templates in app/templates/rstream/ und app/templates/sections/ lesen daraus.
 * Reine Texte werden beim Ausgeben escaped – kein HTML eintragen.
 *
 * Fakten (Stand Oktober 2026): gleicher Medieninhaber wie reichi.com, E-Mail office@rstream.at,
 * gleiche Telefonnummer und Anschrift, seit 2007 im Live-Geschäft, Thema Live-Video-Streaming
 * und Produktion (alte Website: „Live streaming.“). Keine Referenzen, Zahlen, Sender oder
 * Gerätelisten erfinden – offene Punkte stehen in MIGRATION.md.
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

return [

    // ------------------------------------------------------------------
    // Grunddaten
    // ------------------------------------------------------------------
    'site' => [
        // Textfassung der Marke (aria-label, og:site_name, Strukturdaten). Kopf- und Fußzeile
        // zeigen das Sechseck-Zeichen (logo_mark) und daneben „STREAM“ als Bild – das Sechseck
        // mit dem R ist im Original-Logo das „R“ von R-STREAM.
        'brand' => 'rstream',
        'brand_suffix' => '.at',
        // Wortmarke als Bild (aus dem Original-Logo nachgezeichnet, tools/build-rstream-brand.py –
        // das Skript gibt die Größe aus); Pfad relativ zu assets/
        'brand_wordmark' => 'images/rstream-wordmark.svg',
        'brand_wordmark_size' => [560, 93],
        'name' => 'Christian Reichinger',
        'title' => 'rstream.at – Livestream-Produktion: Mehrkamera, Bildregie & Übertragung für Konzerte und Events',
        'description' => 'Livestream-Produktion von Christian „reichi“ Reichinger: Mehrkamera-Aufnahme, Bildregie und Übertragung für Konzerte, Festivals, Firmen-Events und Konferenzen – mit Aufzeichnung für danach. Der Streaming-Zweig von reichi.com.',
        'locale' => 'de_AT',
        'lang' => 'de',
        'share_image' => '/assets/images/share-rstream.png',
        'share_image_width' => 1200,
        'share_image_height' => 630,
        'share_image_alt' => 'rstream.at – Livestream-Produktion: Mehrkamera, Bildregie und Übertragung',
        // Dunkles Farbschema wie reichi.com, eigene Stylesheet-/Script-Ergänzungen zusätzlich zu den Basisdateien
        'theme_color' => '#0b0b10',
        'color_scheme' => 'dark',
        'form_theme' => 'dark',
        'stylesheets' => ['css/style.css', 'css/rstream.css'],
        'scripts' => ['js/main.js', 'js/rstream.js'],
        'og_type_home' => 'website',
        // Strukturdaten: dieselbe Person wie auf reichi.com
        'job_title' => 'Livestream-Produzent, Tontechniker / Sound Engineer, Tourmanager',
        'knows_about' => ['Live Streaming', 'Multi-Camera Production', 'Video Switching', 'Live Sound Mixing', 'Tourmanagement'],
        'person_image' => 'https://www.reichi.com/assets/images/photos/portrait-hood-800.jpg',
        'same_as' => ['https://www.reichi.com/'],
        'website_alternate_names' => ['R-Stream', 'rstream.at – Livestream-Produktion'],
    ],

    // ------------------------------------------------------------------
    // Kontakt- und Geschäftsdaten – gleicher Medieninhaber wie reichi.com
    // ------------------------------------------------------------------
    'contact' => [
        'name' => 'Christian Reichinger',
        'street' => 'Maria Aich 3',
        'zip' => '4971',
        'city' => 'Aurolzmünster',
        'district' => 'Ried im Innkreis',
        'country' => 'Österreich',
        'email' => 'office@rstream.at',
        'phone_display' => '+43 664 4385462',
        'phone_href' => '+436644385462',
        'uid' => 'ATU67362668',
        'roles' => 'Livestream-Produktion: Mehrkamera, Bildregie, Übertragung und Aufzeichnung für Konzerte, Events und Konferenzen.',
        'region' => 'Innviertel / Oberösterreich / Bayern – und überall dort, wo dein Event stattfindet',
        'availability' => 'Der Streaming-Zweig von reichi.com.',
        'alias_domains' => [],
    ],

    'social' => [
        ['label' => 'Instagram', 'handle' => '@the_reichi', 'url' => 'https://www.instagram.com/the_reichi/', 'icon' => 'instagram'],
    ],

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    'nav' => [
        ['label' => 'Leistungen', 'href' => '/#leistungen'],
        ['label' => 'Ablauf', 'href' => '/#ablauf'],
        ['label' => 'Warum ich', 'href' => '/#warum'],
        ['label' => 'Einsatzbereiche', 'href' => '/#einsatz'],
        ['label' => 'Projekte', 'href' => '/#projekte'],
        ['label' => 'Kontakt', 'href' => '/#contact'],
    ],
    'nav_cta' => ['label' => 'Anfrage', 'href' => '/#anfrage'],

    // ------------------------------------------------------------------
    // Hero
    // ------------------------------------------------------------------
    'hero' => [
        'eyebrow' => 'Livestream-Produktion aus dem Innviertel',
        'headline_a' => 'Dein Event. Live.',
        'headline_b' => 'Mehrkamera, Bildregie, Stream.',
        'tagline' => 'Live is live – no second take.',
        'intro' => 'Ich bin Christian, seit 2007 mit Bands unterwegs – und der, der dein Konzert, dein Event oder deine Konferenz sauber ins Netz bringt. rstream.at ist der Streaming-Zweig von reichi.com: Kameras, Bildmischer, Ton und Übertragung aus einer Hand, mit Aufzeichnung für danach.',
        'chips' => ['Mehrkamera', 'Bildregie', 'Übertragung', 'Aufzeichnung'],
        'primary' => ['label' => 'Anfrage senden', 'href' => '#anfrage'],
        'secondary' => ['label' => 'Was ich mache', 'href' => '#leistungen'],
        // Multiviewer rechts: Kopfzeile, Programm- und Vorschaubild, vier Kamerakacheln mit Tally,
        // Pegel und ein Terminal-Streifen. Die Bilder sind stilisierte Szenen (CSS), keine Fotos.
        'mv_label' => 'Stilisierter Multiviewer einer Livestream-Regie: Programmbild, Vorschau, vier Kameras mit Tally-Anzeige, Tonpegel und Statuszeilen',
        'mv_live' => 'Live',
        'mv_program' => 'PGM',
        'mv_preview' => 'PVW',
        'mv_encoder' => '1080p50 · stabil',
        // Kameras in der Reihenfolge der Kacheln; 'scene' wählt die stilisierte Szene in rstream.css
        'mv_cams' => [
            ['name' => 'Cam 1', 'shot' => 'Totale', 'scene' => 'wide'],
            ['name' => 'Cam 2', 'shot' => 'Gesang', 'scene' => 'vocals'],
            ['name' => 'Cam 3', 'shot' => 'Drums', 'scene' => 'drums'],
            ['name' => 'Cam 4', 'shot' => 'Publikum', 'scene' => 'crowd'],
        ],
        'mv_audio' => ['L', 'R'],
        'mv_caption' => 'Regie & Übertragung, schematisch',
        'mv_meta' => 'On air',
        // Terminal-Streifen unter dem Multiviewer. Mit JavaScript werden die Zeilen nacheinander
        // getippt (Schleife), ohne JavaScript oder mit reduzierter Bewegung stehen die ersten drei fest.
        // Nur Aussagen, die auch im übrigen Text stehen – keine erfundenen Zahlen.
        'log_lines' => [
            'cams: 4 inputs · tally ok',
            'audio: embedded · stereo',
            'encoder: 1080p50 · bitrate stabil',
            'stream: primary up · backup standby',
            'record: iso + program · läuft',
            'regie: alles grün · keine alarme',
        ],
        // Meldungen der simulierten Regie; {cam} ist die Kamera, {kind} die Art des Schnitts.
        'log_events' => [
            'cut' => '{kind} → {cam}',
            'cut_kind' => 'cut',
            'mix_kind' => 'mix',
        ],
    ],

    // ------------------------------------------------------------------
    // Leistungen
    // ------------------------------------------------------------------
    'services' => [
        'eyebrow' => 'What I do',
        'title' => 'Drei Dinge, die ich für dein Event tun kann.',
        'note' => 'Einzeln buchbar oder als Paket – vom ersten Gespräch bis zum fertigen Mitschnitt.',
        'items' => [
            [
                'id' => 'produzieren',
                'name' => 'Produzieren',
                'tag' => 'Regie',
                'text' => 'Mehrere Kameras, ein Bild: Ich plane die Kamerapositionen, mische live und halte den Ton sauber.',
                'points' => [
                    'Mehrkamera-Produktion mit Bildregie – von der Totalen bis zur Nahaufnahme',
                    'Ton direkt vom Pult oder eigene Abnahme, stereo in den Stream',
                    'Einblendungen: Titel, Bauchbinden, Logos, Programm-Hinweise',
                    'Kamerateam nach Bedarf, auf Wunsch auch ferngesteuerte Kameras',
                ],
            ],
            [
                'id' => 'uebertragen',
                'name' => 'Übertragen',
                'tag' => 'Stream',
                'text' => 'Vom Encoder bis zur Plattform – inklusive Internet-Anbindung vor Ort und Reserve.',
                'points' => [
                    'Encoding und Ausspielung zu YouTube, Vimeo, Facebook oder deinem eigenen Player',
                    'Internet am Gelände: Leitung, Bündelung und zweite Leitung als Reserve (gemeinsam mit reichi.it)',
                    'Hybrid-Events: Zuschauer vor Ort und im Netz, Zuschaltungen von außen',
                    'Monitoring der Übertragung während der gesamten Sendung',
                ],
            ],
            [
                'id' => 'festhalten',
                'name' => 'Festhalten',
                'tag' => 'Record',
                'text' => 'Was live war, bleibt: Aufzeichnung aller Kameras und des Programmbilds.',
                'points' => [
                    'Aufzeichnung des Programmbilds und der einzelnen Kameras (ISO)',
                    'Kurzer Schnitt für danach: Konzertmitschnitt, Highlight-Clip, Social-Media-Fassung',
                    'Übergabe in den Formaten, die du brauchst – Download oder Datenträger',
                    'Auf Wunsch Mitschnitt ohne Stream, nur fürs Archiv',
                ],
            ],
        ],
    ],

    // ------------------------------------------------------------------
    // Ablauf
    // ------------------------------------------------------------------
    'process' => [
        'eyebrow' => 'How it works',
        'title' => 'So läuft’s.',
        'note' => 'Kein Produktions-Theater. Vier Schritte, klar abgesprochen.',
        'steps' => [
            ['name' => 'Briefing', 'text' => 'Du erzählst mir, was geplant ist: Wann, wo, wie viele Kameras, wohin soll der Stream. Telefon, Video oder vor Ort.'],
            ['name' => 'Konzept & Technik', 'text' => 'Ich skizziere Kamerapositionen, Ton, Encoder und Leitung – und sage dir ehrlich, was nötig ist und was nicht.'],
            ['name' => 'Probe & Sendung', 'text' => 'Aufbau, Verkabelung, Testsendung. Dann läuft die Show: Bildregie, Pegel und Übertragung im Blick.'],
            ['name' => 'Aufzeichnung & Übergabe', 'text' => 'Nach dem Event bekommst du den Mitschnitt – und wenn gewünscht einen kurzen Schnitt für Social Media.'],
        ],
    ],

    // ------------------------------------------------------------------
    // Warum ich
    // ------------------------------------------------------------------
    'why' => [
        'eyebrow' => 'Why me',
        'title' => 'Ich kenne die Bühne – nicht nur den Bildmischer.',
        'lead' => 'Seit 2007 bin ich mit Bands auf Tour, als Tontechniker und Tourmanager. Ich weiß, wie ein Showtag tickt: wann der Soundcheck ist, wann es ernst wird und wie ein Konzert im Stream klingen und aussehen muss, damit man dranbleibt.',
        'points' => [
            ['name' => 'Eine Ansprechperson', 'text' => 'Kameras, Ton, Stream und Leitung landen bei einem Menschen, der das Ganze im Blick hat.'],
            ['name' => 'Ton, der passt', 'text' => 'Ein Stream ist nur so gut wie sein Ton. Der kommt bei mir vom Fach – FOH-Erfahrung seit 2007.'],
            ['name' => 'Für jede Größe', 'text' => 'Vom Vereinskonzert mit zwei Kameras bis zum Festival mit Regieplatz – skalierbar, ohne Aufwand-Theater.'],
            ['name' => 'Netz & Stream aus einer Hand', 'text' => 'Wenn die Leitung am Gelände fehlt, baue ich sie mit reichi.it gleich mit auf.'],
        ],
        'facts' => [
            ['label' => 'Seit', 'value' => '2007'],
            ['label' => 'Basis', 'value' => 'Innviertel, OÖ'],
            ['label' => 'Einsatz', 'value' => 'Wo das Event ist'],
        ],
        'quote' => 'Live is live – no second take.',
    ],

    // ------------------------------------------------------------------
    // Einsatzbereiche – Karten (Template rstream/usecases.php, Klassen aus 9b.4 in style.css).
    // Keine Referenzen: bestätigte Produktionen stehen noch aus (MIGRATION.md).
    // ------------------------------------------------------------------
    'usecases' => [
        'eyebrow' => 'Where it works',
        'title' => 'Einsatzbereiche',
        'note' => 'Was gestreamt wird, ist egal – wichtig ist, dass es live funktioniert.',
        'items' => [
            [
                'kind' => 'Musik',
                'name' => 'Konzerte & Festivals',
                'text' => 'Mehrkamera-Stream mit Ton vom Pult, Bauchbinden mit Band und Songtitel, Mitschnitt fürs Archiv oder die nächste Promo.',
            ],
            [
                'kind' => 'Business',
                'name' => 'Firmen-Events & Hybrid-Meetings',
                'text' => 'Kick-offs, Produktvorstellungen, Jubiläen: Zuschauer vor Ort und im Netz, Zuschaltungen von außen, geschützter Zugang auf Wunsch.',
            ],
            [
                'kind' => 'Kultur',
                'name' => 'Kultur & Vereine',
                'text' => 'Theater, Blasmusik, Chor, Vereinsabend – klein genug für zwei Kameras, groß genug, dass es gut aussieht.',
            ],
            [
                'kind' => 'Wissen',
                'name' => 'Konferenzen & Vorträge',
                'text' => 'Rednerbild und Präsentation parallel, saubere Sprachverständlichkeit, Aufzeichnung je Vortrag für die Mediathek.',
            ],
        ],
        'more_label' => 'Bühnen-Referenzen seit 2007 stehen auf reichi.com',
        'more_url' => 'https://www.reichi.com/#portfolio',
    ],

    // ------------------------------------------------------------------
    // Verbundene Projekte – gleiches Template wie auf reichi.com (sections/projects.php).
    // Logos liegen als Kopien in rstream/assets/images/logos/ (tools/sync-site-assets.sh);
    // auf dem dunklen Grund bleiben die hellen Logos wie sie sind.
    // ------------------------------------------------------------------
    'projects' => [
        'eyebrow' => 'Network',
        'title' => 'Verbundene Projekte',
        'items' => [
            [
                'id' => 'reichi-com',
                'name' => 'reichi.com',
                'text' => 'Tontechnik & Touring: FOH, Monitor und Tourmanagement – das Hauptgeschäft seit 2007.',
                'url' => 'https://www.reichi.com/',
                'link_label' => 'reichi.com',
                // Wortmarke als Text mit dem Sechseck-Zeichen (logo_mark), kein Bild
                'logo' => ['brand' => 'reichi', 'suffix' => '.com', 'alt' => 'reichi.com'],
            ],
            [
                'id' => 'reichi-it',
                'name' => 'reichi.it',
                'text' => 'Event-IT: Netzwerk, WLAN und Internet-Uplink am Gelände – die Leitung für den Stream.',
                'url' => 'https://reichi.it/',
                'link_label' => 'reichi.it',
                // Helle Fassung (tools/build-it-brand.py) – passt so auf den dunklen Grund
                'logo' => ['file' => 'reichi-it.png', 'width' => 760, 'height' => 175, 'alt' => 'reichi.it'],
            ],
            [
                'id' => 'bleedingstar',
                'name' => 'BleedingStar',
                'text' => 'Label, Distribution, Rental and Services.',
                'url' => 'http://www.bleedingstar.at',
                'link_label' => 'bleedingstar.at',
                // Weißes Logo auf transparent – passt so auf den dunklen Grund
                'logo' => ['file' => 'bleedingstar.png', 'width' => 508, 'height' => 148, 'alt' => 'BleedingStar'],
            ],
        ],
    ],

    // ------------------------------------------------------------------
    // Anfrage / Kontakt
    // ------------------------------------------------------------------
    'hire' => [
        'id' => 'anfrage',
        'eyebrow' => 'Say hi',
        'title' => 'Erzähl mir von deinem Event.',
        'question' => 'Want your show to look as good online as it sounds in the room? Let’s talk.',
        'paragraphs' => [
            'Egal ob du nur wissen willst, was ein Stream für dein Konzert kostet, oder jemanden brauchst, der Kameras, Ton und Übertragung komplett übernimmt: Schreib mir ein paar Zeilen, ich melde mich.',
            'Je früher, desto entspannter – aber auch kurzfristig lässt sich meist etwas machen.',
        ],
        'form' => [
            'name' => 'Name',
            'email' => 'E-Mail-Adresse',
            'phone' => 'Telefonnummer',
            'phone_hint' => 'optional',
            'subject' => 'Worum geht’s?',
            'subject_placeholder' => 'Bitte wählen',
            'subject_options' => ['Livestream', 'Aufzeichnung', 'Hybrid-Event', 'Sonstiges'],
            'message' => 'Nachricht',
            'message_hint' => 'Wann, wo, wie viele Kameras – und wohin soll der Stream? Stichworte reichen.',
            'submit' => 'Anfrage senden',
            'privacy_note' => 'Die Angaben werden ausschließlich zur Bearbeitung der Anfrage per E-Mail übermittelt. Details in der Datenschutzerklärung.',
        ],
    ],

    'contact_section' => [
        'title' => 'Kontakt',
    ],

    'footer' => [
        'credits' => null,
        'links' => [
            ['label' => 'reichi.com', 'note' => 'Tontechnik & Touring', 'url' => 'https://www.reichi.com/', 'me' => true],
            ['label' => 'reichi.it', 'note' => 'Event-IT', 'url' => 'https://reichi.it/', 'me' => true],
        ],
    ],

    // Website-spezifische Formulierungen in Impressum und Datenschutzerklärung
    'legal' => [
        'impressum_notes' => [
            'rstream.at ist der Streaming-Zweig von reichi.com und wird vom selben Medieninhaber betrieben.',
        ],
        'form_section' => 'Anfrage',
        'external_examples' => 'reichi.com, reichi.it, Instagram, die Streaming-Plattform einer Veranstaltung',
    ],

    'image_credits' => [],
];
