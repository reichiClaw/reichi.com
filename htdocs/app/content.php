<?php
/**
 * reichi.com – Inhalte
 *
 * Diese Datei ist die einzige Stelle, an der Texte, Referenzen, Links und
 * Bildangaben gepflegt werden. Die Templates in app/templates/ lesen nur daraus.
 *
 * Konventionen
 *  - Reine Texte werden beim Ausgeben automatisch escaped; hier also kein HTML eintragen.
 *  - Fotos liegen in htdocs/assets/images/photos/ als <name>-<breite>.jpg/.webp.
 *    'widths' listet die tatsächlich vorhandenen Breiten; 'width'/'height' sind die
 *    Maße der größten Variante (für das Seitenverhältnis).
 *  - Logos liegen in htdocs/assets/images/logos/. 'light_bg' = Logo bringt einen
 *    hellen Hintergrund mit und wird auf eine helle Fläche gesetzt.
 *  - Neue Fotos: Original nach assets-src/originals/ legen, tools/build-images.py
 *    erweitern und ausführen, dann hier eintragen (siehe README.md).
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
        'brand' => 'reichi',
        'name' => 'Christian Reichinger',
        'title' => 'Christian Reichinger – Tontechniker & Tourmanager | reichi',
        'description' => 'Christian „reichi“ Reichinger: Live-Tontechniker (FOH) und Tourmanager aus Ried im Innkreis, Oberösterreich. Seit 2007 mit Bands auf Tour – Festivals, Clubs, international. Anfragen für Tour, Konzert oder Recording.',
        'locale' => 'de_AT',
        'lang' => 'de',
        // Bild für Social-Sharing (relativer Pfad unter htdocs/)
        'share_image' => '/assets/images/photos/stage-red-1600.jpg',
        'share_image_width' => 1600,
        'share_image_height' => 1067,
    ],

    // ------------------------------------------------------------------
    // Kontakt- und Geschäftsdaten (Quelle: alte Website + Impressum, vom Inhaber zu bestätigen)
    // ------------------------------------------------------------------
    'contact' => [
        'name' => 'Christian Reichinger',
        'street' => 'Maria Aich 3',
        'zip' => '4971',
        'city' => 'Aurolzmünster',
        'district' => 'Ried im Innkreis',
        'country' => 'Österreich',
        'email' => 'reichi@reichi.com',
        'phone_display' => '+43 664 4385462',
        'phone_href' => '+436644385462',
        'uid' => 'ATU67362668',
        'roles' => 'Tontechniker, Live-Techniker, Sound Engineer, Sound Designer, Tour Manager, Label-Betreiber, Instagramer aus Ried im Innkreis / Oberösterreich.',
        'region' => 'Ried im Innkreis / Innviertel / Oberösterreich / Upper Austria / Bayern',
        'availability' => 'Worldwide available for jobs.',
        // Weitere Domains laut altem Impressum
        'alias_domains' => [
            'www.christianreichinger.at',
            'www.christianreichinger.com',
            'www.reichi.at',
            'www.reichi.com',
        ],
    ],

    'social' => [
        ['label' => 'Instagram', 'handle' => '@the_reichi', 'url' => 'https://www.instagram.com/the_reichi/', 'icon' => 'instagram'],
    ],

    // ------------------------------------------------------------------
    // Navigation (Anker der alten Website bleiben erhalten)
    // ------------------------------------------------------------------
    'nav' => [
        ['label' => 'Skills', 'href' => '/#skills'],
        ['label' => 'Über mich', 'href' => '/#about'],
        ['label' => 'Referenzen', 'href' => '/#portfolio'],
        ['label' => 'Fotos', 'href' => '/#fotos'],
        ['label' => 'Projekte', 'href' => '/#projekte'],
        ['label' => 'Kontakt', 'href' => '/#contact'],
    ],
    'nav_cta' => ['label' => 'Anfrage', 'href' => '/#hire'],

    // ------------------------------------------------------------------
    // Hero
    // ------------------------------------------------------------------
    'hero' => [
        'eyebrow' => 'Christian Reichinger',
        'headline' => 'reichi',
        'roles' => ['Sound Engineer', 'Tour Manager', 'Instagramer'],
        'intro' => 'Tontechniker aus Ried im Innkreis, Oberösterreich. Seit 2007 mit Bands und Künstlern unterwegs – von großen Festivals bis zu kleinen Clubs, international und mit klarem Fokus auf Live-Mischung und Touring.',
        'primary' => ['label' => 'Referenzen ansehen', 'href' => '#portfolio'],
        'secondary' => ['label' => 'Anfrage senden', 'href' => '#hire'],
        'photo' => 'portrait-hood',
        'location' => 'Ried im Innkreis · Oberösterreich',
    ],

    // ------------------------------------------------------------------
    // Skills
    // ------------------------------------------------------------------
    'skills' => [
        'title' => 'Knowledge and experience is the key!',
        'claim' => 'Live-Mischung, Tourmanagement und alles dazwischen',
        'focus' => 'Focused on mixing and touring with Bands and Artists!',
        'items' => [
            [
                'name' => 'Live Sound Mixing',
                'text' => 'FOH-Mischung für Bands und Künstler – auf Festivals, in Clubs und überall dort, wo eine Show stattfindet. Auch mit knappem Equipment.',
            ],
            [
                'name' => 'Live Sound Recording',
                'text' => 'Mitschnitt von Live-Shows direkt vom Pult.',
            ],
            [
                'name' => 'Travel Organisation',
                'text' => 'Planung und Organisation der Reise für Bands auf Tour – gewachsen aus vielen Jahren als Roadie und Fahrer.',
            ],
            [
                'name' => 'Managing',
                'text' => 'Tourmanagement: Ablauf, Koordination und Ansprechperson vor Ort, damit sich die Band auf die Musik konzentrieren kann.',
            ],
            [
                'name' => 'Publishing',
                'text' => 'Label-Arbeit und Veröffentlichung über BleedingStar – Label, Distribution, Rental und Services.',
            ],
        ],
    ],

    // ------------------------------------------------------------------
    // Über mich / Werdegang (aus „personal resume“ und „professional background“ zusammengeführt)
    // ------------------------------------------------------------------
    'about' => [
        'eyebrow' => 'Über mich',
        'title' => 'Sound Engineer, Roadie, Driver – and many more …',
        'lead' => 'Wie ließen sich Musik und Reisen besser verbinden, als mit Bands und Künstlern als Tontechniker unterwegs zu sein?',
        'paragraphs' => [
            'Angefangen hat alles 2007, als ich begann, eine Band zu begleiten – auf Tour, als Manager, Roadie oder Fahrer. Über die Jahre wurden die Veranstaltungen größer und die Anzahl der Bands nahm zu. Im Bus war kein Platz mehr für einen eigenen Tontechniker. Ich bin in diese Rolle hineingerutscht und habe mich auf Anhieb wohlgefühlt.',
            'Seit damals bin ich gemeinsam mit Bands auf Tour. Von großen Festivals bis zu ganz kleinen Clubs oder auch Wohnzimmern habe ich nichts ausgelassen – heute international mit Künstlern unterwegs. Von dieser ganz speziellen Mischung profitiert jeder Musiker.',
        ],
        'facts' => [
            ['label' => 'Seit', 'value' => '2007'],
            ['label' => 'Basis', 'value' => 'Ried im Innkreis, OÖ'],
            ['label' => 'Einsatz', 'value' => 'Worldwide'],
        ],
        'photo' => 'stage-red',
        'quote' => 'Touring is my passion.',
        'cta' => ['label' => 'Anfrage senden', 'href' => '#hire'],
    ],

    // ------------------------------------------------------------------
    // Referenzen (Kategorien exakt wie auf der alten Website getrennt)
    // ------------------------------------------------------------------
    'portfolio' => [
        'title' => 'Referenzen',
        'claim' => 'Things I’ve done in the past!',
        'groups' => [
            [
                'id' => 'foh',
                'title' => 'FOH gemischt für',
                'source_title' => 'I mixed FOH for:',
                'items' => [
                    'Max the Sax', 'Supervision', 'SoulSanity', 'Lumidee', 'Soul Delight', 'Noemi Waysfeld',
                    'The Grandmas', 'Yet Another Floyd', 'i Tüpfe Rider', 'Red Machete', 'Hoamspü', 'Blonder Engel',
                    'Delaytanten', 'His Name is Sandusky', 'Julian David', 'The Rockies', 'XDream', 'Woxx',
                ],
            ],
            [
                'id' => 'collab',
                'title' => 'Zusammengearbeitet mit',
                'source_title' => 'I worked together with:',
                'items' => [
                    'Moop Mama', 'Turbobier', 'LaBrassBanda', 'Stefan Dettl', 'Vaunted', 'Promise',
                    'Back to Felicity', 'Loving the Alien', 'Jacobs Moor',
                ],
            ],
            [
                'id' => 'venues',
                'title' => 'Festivals & Venues',
                'source_title' => 'I worked at:',
                'items' => [
                    'Woodstock der Blasmusik', 'Chiemsee Reggae Festival', 'SummArock Festival',
                    'Freundlich & Kompetent (Hamburg)', 'Kufstein unlimited', 'Zipf Air', 'Bludenzer Jazztage',
                    'Boom Festival', 'Wurm Festival', 'Spinnerei Traun', 'Club El Centro (Tiflis)', 'Club Doors (Tiflis)',
                    'Club Horizont (Varna)', 'Metronome Festival (Prag)', 'Jump & Rock Festival', 'Kik (Ried)',
                    'Krone Fest (Linz)', 'Sommerfest (Hagenberg)', 'Intourist (Batumi)', 'Scorpios (Mykonos)',
                ],
            ],
        ],
    ],

    // Logos aus dem Abschnitt „Clients“ der alten Website (Reihenfolge wie dort)
    'clients' => [
        'title' => 'Kunden',
        'claim' => 'I rocked the crowd with these cool clients!',
        'items' => [
            ['name' => 'Supervision', 'url' => 'https://www.supervision-music.at/', 'file' => 'supervision-400.png', 'file2x' => 'supervision-800.png', 'width' => 400, 'height' => 70, 'alt' => 'Supervision', 'light_bg' => false],
            ['name' => 'Max the Sax', 'url' => 'https://www.maxthesax.at/', 'file' => 'max-the-sax-400.png', 'file2x' => 'max-the-sax-800.png', 'width' => 400, 'height' => 118, 'alt' => 'Max the Sax', 'light_bg' => true],
            ['name' => 'SoulSanity', 'url' => 'https://www.soul-sanity.com/', 'file' => 'soulsanity.png', 'width' => 131, 'height' => 100, 'alt' => 'SoulSanity', 'light_bg' => false],
            ['name' => 'Summ A Rock', 'url' => null /* summa-rock.de existiert nicht mehr (Stand 24.09.2026) */, 'file' => 'summarock-400.jpg', 'file2x' => 'summarock-800.jpg', 'width' => 400, 'height' => 90, 'alt' => 'SummArock Festival', 'light_bg' => false],
            ['name' => 'Sonic VT', 'url' => 'https://sonic-vt.at/', 'file' => 'sonic-vt.png', 'width' => 287, 'height' => 88, 'alt' => 'Sonic Veranstaltungstechnik Pröll', 'light_bg' => false],
            ['name' => 'Scorpios', 'url' => 'https://scorpios.com/', 'file' => 'scorpios.jpg', 'width' => 181, 'height' => 173, 'alt' => 'Scorpios Mykonos', 'light_bg' => false],
            ['name' => 'i Tüpfe Rider', 'url' => 'https://ituepferider.at/', 'file' => 'i-tuepfe-rider-400.png', 'file2x' => 'i-tuepfe-rider-800.png', 'width' => 400, 'height' => 78, 'alt' => 'i Tüpfe Rider', 'light_bg' => false],
            ['name' => 'Stadlmusi', 'url' => 'http://stadlmusi.at', 'file' => 'stadlmusi.jpg', 'width' => 300, 'height' => 200, 'alt' => 'Stadlmusi – Heavy Blasmusik', 'light_bg' => true],
            ['name' => 'Hoamspü', 'url' => 'https://www.hoamspue.at', 'file' => 'hoamspue-400.jpg', 'file2x' => 'hoamspue-800.jpg', 'width' => 400, 'height' => 142, 'alt' => 'Hoamspü – Austropop mit Gfühl', 'light_bg' => true],
            ['name' => 'Freundlich & Kompetent', 'url' => null, 'file' => 'freundlich-kompetent.png', 'width' => 145, 'height' => 135, 'alt' => 'Bar Freundlich & Kompetent, Hamburg', 'light_bg' => false],
            ['name' => 'His Name is Sandusky', 'url' => 'https://www.facebook.com/sandusky.live/', 'file' => 'his-name-is-sandusky.jpg', 'width' => 277, 'height' => 101, 'alt' => 'His Name is Sandusky', 'light_bg' => false],
            ['name' => 'Prague Metronome Festival', 'url' => null, 'file' => 'prague-metronome-festival.png', 'width' => 442, 'height' => 161, 'alt' => 'Prague Metronome Festival', 'light_bg' => false],
        ],
    ],

    // ------------------------------------------------------------------
    // Fotos (alle von der alten Website übernommen; Credits laut Impressum bzw. Bilddaten)
    // ------------------------------------------------------------------
    'photos' => [
        'portrait-hood' => [
            'file' => 'portrait-hood', 'widths' => [480, 600, 800, 1200], 'width' => 1200, 'height' => 1200,
            'alt' => 'Porträt von Christian Reichinger in Schwarz-Weiß, mit Kapuze und Brille',
            'caption' => 'reichi | Christian Reichinger',
            'credit' => 'Martin Mühlbacher – mdot.at',
        ],
        'portrait-studio' => [
            'file' => 'portrait-studio', 'widths' => [800, 1200, 1600], 'width' => 1600, 'height' => 1068,
            'portrait' => ['widths' => [480, 800, 1000], 'width' => 1000, 'height' => 1250],
            'alt' => 'Christian Reichinger im schwarzen T-Shirt mit verschränkten Armen vor grauem Studiohintergrund',
            'caption' => 'Christian Reichinger',
            'credit' => 'Martin Mühlbacher – mdot.at',
        ],
        'stage-red' => [
            'file' => 'stage-red', 'widths' => [640, 1024, 1600, 2048], 'width' => 2048, 'height' => 1365,
            'portrait' => ['widths' => [540, 900], 'width' => 900, 'height' => 1125],
            'alt' => 'Band auf einer Bühne in rotem Licht mit Lichtkegeln über dem Publikum',
            'caption' => 'Live-Show in rotem Licht',
            'credit' => null,
        ],
        'crowd-club' => [
            'file' => 'crowd-club', 'widths' => [480, 900], 'width' => 900, 'height' => 601,
            'alt' => 'Dicht gedrängtes Publikum in einem Club, ein Besucher hebt den Arm',
            'caption' => 'Publikum im Club',
            'credit' => 'Martin Mühlbacher – mdot.at',
        ],
        'stage-blue' => [
            'file' => 'stage-blue', 'widths' => [480, 900], 'width' => 900, 'height' => 600,
            'alt' => 'Open-Air-Bühne in blauem Licht mit Lichtkegeln, davor ein großes Publikum',
            'caption' => 'Open-Air-Bühne bei Nacht',
            'credit' => 'StageShots.at | Christian Reichinger',
        ],
        'max-the-sax-prag-1' => [
            'file' => 'max-the-sax-prag-1', 'widths' => [480, 800, 1200], 'width' => 1200, 'height' => 800,
            'alt' => 'Zwei Musiker auf einer rot beleuchteten Bühne, davor Silhouetten des Publikums',
            'caption' => 'FOH für Max the Sax am Metronome Festival Prag',
            'credit' => 'MW Design – michaelawiesinger.at',
        ],
        'max-the-sax-prag-2' => [
            'file' => 'max-the-sax-prag-2', 'widths' => [480, 800, 1200], 'width' => 1200, 'height' => 800,
            'alt' => 'Weite Halle mit Bühne im Gegenlicht und großem Publikum',
            'caption' => 'FOH für Max the Sax am Metronome Festival Prag',
            'credit' => 'MW Design – michaelawiesinger.at',
        ],
        'max-the-sax-prag-3' => [
            'file' => 'max-the-sax-prag-3', 'widths' => [480, 800, 1200], 'width' => 1200, 'height' => 800,
            'alt' => 'Band vor einer großen LED-Wand, rot beleuchtete Bühne',
            'caption' => 'FOH für Max the Sax am Metronome Festival Prag',
            'credit' => 'MW Design – michaelawiesinger.at',
        ],
    ],

    // Reihenfolge und Größe der Galerie; 'size' => 'wide' belegt zwei Spalten
    'gallery' => [
        'eyebrow' => 'Fotos',
        'title' => 'Von der Bühne',
        'text' => 'Ausgewählte Bilder von Shows und Touren.',
        'items' => [
            ['photo' => 'max-the-sax-prag-2', 'size' => 'wide'],
            ['photo' => 'max-the-sax-prag-1', 'size' => 'normal'],
            ['photo' => 'crowd-club', 'size' => 'normal'],
            ['photo' => 'stage-blue', 'size' => 'normal'],
            ['photo' => 'max-the-sax-prag-3', 'size' => 'normal'],
        ],
    ],

    // ------------------------------------------------------------------
    // Verbundene Projekte
    // ------------------------------------------------------------------
    'projects' => [
        'eyebrow' => 'Projekte',
        'title' => 'Verbundene Projekte',
        'items' => [
            [
                'id' => 'bleedingstar',
                'name' => 'BleedingStar',
                'text' => 'Label, Distribution, Rental and Services.',
                'url' => 'http://www.bleedingstar.at',
                'link_label' => 'bleedingstar.at',
                'logo' => ['file' => 'bleedingstar.png', 'width' => 508, 'height' => 148, 'alt' => 'BleedingStar'],
            ],
            [
                'id' => 'rstream',
                'name' => 'R-Stream',
                'text' => 'Live streaming.',
                'url' => 'https://www.rstream.at/',
                'link_label' => 'rstream.at',
                // Das Original-Logo ist schwarz auf transparent; 'invert' stellt es per CSS weiß dar.
                'logo' => ['file' => 'rstream.png', 'width' => 744, 'height' => 182, 'alt' => 'R-Stream', 'invert' => true],
            ],
        ],
    ],

    // ------------------------------------------------------------------
    // Anfrage / Kontakt
    // ------------------------------------------------------------------
    'hire' => [
        'eyebrow' => 'Anfrage',
        'title' => 'Tontechniker oder Tourmanager für deine nächste Tour oder Show',
        'question' => 'Hire me for your next Tour or Concert!',
        'paragraphs' => [
            'Egal ob Tour oder einzelnes Konzert – du bist hier richtig!',
            'Sei es auf einem Festival mit perfektem Setup, das gute Planung im Voraus benötigt, oder am Rande des Schwarzen Meers mit sehr limitiertem Equipment: Gerne hole ich für euch das Beste aus jeder Show heraus.',
        ],
        'photo' => 'portrait-studio',
        'form' => [
            'name' => 'Name',
            'email' => 'E-Mail-Adresse',
            'phone' => 'Telefonnummer',
            'phone_hint' => 'optional',
            'subject' => 'Betreff',
            'message' => 'Nachricht',
            'message_hint' => 'Wann, wo, was – Tour, Konzert, Festival, Recording? Je konkreter, desto besser.',
            'submit' => 'Anfrage senden',
            'privacy_note' => 'Die Angaben werden ausschließlich zur Bearbeitung der Anfrage per E-Mail übermittelt. Details in der Datenschutzerklärung.',
        ],
    ],

    'contact_section' => [
        'title' => 'Kontakt',
        'direct_title' => 'Direkt erreichen',
    ],

    'footer' => [
        'credits' => 'Fotos: Martin Mühlbacher – mdot.at · MW Design – michaelawiesinger.at · StageShots.at',
    ],

    // Bildnachweise (Impressum), Text laut alter Website, ergänzt um erkennbare Bildquellen
    'image_credits' => [
        'Christian Reichinger / Porträtfotos: Martin Mühlbacher – mdot.at / MW Design – MichaelaWiesinger.at',
        'Bühnenfotos Metronome Festival Prag: MW Design – MichaelaWiesinger.at',
        'Open-Air-Bühne in blauem Licht: StageShots.at | Christian Reichinger',
        'Logos: die jeweiligen Rechteinhaber',
    ],
];
