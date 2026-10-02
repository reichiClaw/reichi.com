<?php
/**
 * reichi.it – Inhalte der IT-Abteilung
 *
 * Gleiche Konventionen wie content.php: nur hier werden Texte und Links gepflegt,
 * die Templates in app/templates/it/ lesen daraus. Reine Texte werden beim Ausgeben
 * escaped – kein HTML eintragen.
 *
 * Fakten (vom Inhaber bestätigt, Oktober 2026): gleicher Medieninhaber wie reichi.com,
 * E-Mail reichi@reichi.it, seit 2007 im Live-Geschäft, UniFi als Hausmarke, mit Cisco und
 * den anderen üblichen Herstellern vertraut, Kunden: Festivals, Produktionsfirmen, Firmen,
 * Referenz: Woodstock der Blasmusik. Keine weiteren Referenzen, Zahlen oder Gerätelisten
 * erfinden – offene Punkte stehen in MIGRATION.md.
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
        'brand_suffix' => '.it',
        // Kopf- und Fußzeile zeigen die Wortmarke als Bild (Ligatur „it“ mit Blitz als Negativform,
        // erzeugt von tools/build-it-brand.py); Pfad relativ zu assets/
        'brand_wordmark' => 'images/reichi-it-wordmark.svg',
        'name' => 'Christian Reichinger',
        'title' => 'reichi.it – Event-IT: Netzwerk, WLAN & Support für Festivals und Events',
        'description' => 'Event-IT von Christian „reichi“ Reichinger: Netzwerk, WLAN und Internet am Gelände aufbauen, während der Show betreuen, vorher richtig planen. Für Festivals, Produktionsfirmen und Firmen-Events. Die IT-Abteilung von reichi.com.',
        'locale' => 'de_AT',
        'lang' => 'de',
        'share_image' => '/assets/images/share-reichi-it.png',
        'share_image_width' => 1200,
        'share_image_height' => 630,
        'share_image_alt' => 'reichi.it – Event-IT: Netzwerk, WLAN und Support für Festivals und Events',
        // Helles Farbschema, eigene Stylesheet-/Script-Ergänzungen zusätzlich zu den Basisdateien
        'theme_color' => '#f5f2eb',
        'color_scheme' => 'light',
        'form_theme' => 'light',
        'stylesheets' => ['css/style.css', 'css/it.css'],
        'scripts' => ['js/main.js', 'js/it.js'],
        'og_type_home' => 'website',
        // Strukturdaten: dieselbe Person wie auf reichi.com
        'job_title' => 'Event-IT-Techniker, Tontechniker / Sound Engineer, Tourmanager',
        'knows_about' => ['Event IT', 'Netzwerktechnik', 'WLAN', 'UniFi', 'Live Sound Mixing', 'Tourmanagement'],
        'person_image' => 'https://www.reichi.com/assets/images/photos/portrait-hood-800.jpg',
        'same_as' => ['https://www.reichi.com/'],
        'website_alternate_names' => ['reichi IT', 'reichi.it – Event-IT'],
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
        'email' => 'reichi@reichi.it',
        'phone_display' => '+43 664 4385462',
        'phone_href' => '+436644385462',
        'uid' => 'ATU67362668',
        'roles' => 'Event-IT: Netzwerk, WLAN, Internet und Support für Festivals, Produktionen und Firmen-Events.',
        'region' => 'Innviertel / Oberösterreich / Bayern – und überall dort, wo dein Event stattfindet',
        'availability' => 'Die IT-Abteilung von reichi.com.',
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
        ['label' => 'Referenz', 'href' => '/#referenz'],
        ['label' => 'Kontakt', 'href' => '/#contact'],
    ],
    'nav_cta' => ['label' => 'Anfrage', 'href' => '/#anfrage'],

    // ------------------------------------------------------------------
    // Hero
    // ------------------------------------------------------------------
    'hero' => [
        'eyebrow' => 'Event-IT aus dem Innviertel',
        'headline_a' => 'IT fürs Event.',
        'headline_b' => 'Aufgebaut, betreut, durchdacht.',
        'tagline' => 'Set up. Run. Done – so you can focus on the show.',
        'intro' => 'Ich bin Christian, seit 2007 mit Bands unterwegs – und der, der am Gelände das Netzwerk zum Laufen bringt. reichi.it ist die IT-Abteilung von reichi.com: Netzwerk, WLAN, Internet und Technik-Support für Festivals, Produktionsfirmen und Firmen-Events.',
        'chips' => ['Netzwerk & WLAN', 'Internet-Uplink', 'Betrieb vor Ort', 'Beratung'],
        'primary' => ['label' => 'Anfrage senden', 'href' => '#anfrage'],
        'secondary' => ['label' => 'Was ich mache', 'href' => '#leistungen'],
        // Beschriftungen der Netzwerk-Grafik (Knoten in der Reihenfolge der Darstellung)
        'diagram_label' => 'Schematischer Netzplan eines Festivalgeländes: Internet-Uplink, Produktionsbüro, FOH, Bühne, Backstage, Kassa und Gäste-WLAN',
        'diagram_nodes' => ['Uplink', 'Produktion', 'FOH', 'Stage', 'Backstage', 'Kassa', 'Guest Wi-Fi'],
        'diagram_caption' => 'Netzplan, schematisch',
        'diagram_meta' => 'All nodes up',
    ],

    // ------------------------------------------------------------------
    // Leistungen
    // ------------------------------------------------------------------
    'services' => [
        'eyebrow' => 'What I do',
        'title' => 'Drei Dinge, die ich für dein Event tun kann.',
        'note' => 'Einzeln buchbar oder als Paket – von der ersten Skizze bis zum Abbau.',
        'items' => [
            [
                'id' => 'aufbauen',
                'name' => 'Aufbauen',
                'tag' => 'Setup',
                'text' => 'Netzwerk, WLAN und Internet am Gelände – vom Uplink bis zur letzten Dose im Backstage.',
                'points' => [
                    'Netzwerkplanung und Verkabelung für Gelände, Hallen und Zelte',
                    'WLAN für Crew, Presse, Kassa und Gäste – getrennt und sauber priorisiert',
                    'Internet-Anbindung vor Ort, auf Wunsch mit zweiter Leitung als Reserve',
                    'Switches, Router, Access Points: UniFi als Hausmarke, Cisco und die anderen üblichen Hersteller ebenso',
                ],
            ],
            [
                'id' => 'betreiben',
                'name' => 'Betreiben',
                'tag' => 'Showtime',
                'text' => 'Während der Veranstaltung bin ich da – am Gelände, nicht nur am Telefon.',
                'points' => [
                    'Monitoring des Netzwerks und schnelle Hilfe, wenn etwas hakt',
                    'Ansprechperson für Produktionsbüro, Kassa, Catering und Technik',
                    'Drucker, Laptops, Scanner, Streaming-Anbindung: kümmere ich mich',
                    'Abbau, Rückbau und eine kurze Doku fürs nächste Mal',
                ],
            ],
            [
                'id' => 'beraten',
                'name' => 'Beraten',
                'tag' => 'Consulting',
                'text' => 'Was braucht dein Event wirklich? Oft weniger als gedacht – aber das richtig.',
                'points' => [
                    'Bedarfsanalyse und Netzwerkkonzept, verständlich erklärt',
                    'Angebote prüfen, Hardware auswählen, Kosten im Blick behalten',
                    'Konzepte für wiederkehrende Events, die man jedes Jahr wieder aufstecken kann',
                    'Zusammenarbeit mit deinem Technikteam und dem Internet-Provider',
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
        'note' => 'Kein Projektplan-Theater. Vier Schritte, klar abgesprochen.',
        'steps' => [
            ['name' => 'Gespräch', 'text' => 'Du erzählst mir, was geplant ist: Wann, wo, wie groß, was muss funktionieren. Telefon, Video oder vor Ort.'],
            ['name' => 'Konzept', 'text' => 'Ich skizziere Netzplan, Hardware und Uplink – und sage dir ehrlich, was nötig ist und was nicht.'],
            ['name' => 'Aufbau & Test', 'text' => 'Verkabeln, konfigurieren, ausleuchten, durchtesten. Fertig, bevor die erste Band Soundcheck hat.'],
            ['name' => 'Show & Abbau', 'text' => 'Während des Events bin ich erreichbar und greifbar. Danach alles wieder ordentlich in die Cases.'],
        ],
    ],

    // ------------------------------------------------------------------
    // Warum ich
    // ------------------------------------------------------------------
    'why' => [
        'eyebrow' => 'Why me',
        'title' => 'Ich kenne das Gelände – nicht nur das Rack.',
        'lead' => 'Seit 2007 bin ich mit Bands auf Tour, als Tontechniker und Tourmanager. Ich weiß, wie ein Showtag tickt: wann Hektik ist, wann nichts ausfallen darf und wer gerade wirklich ein funktionierendes WLAN braucht.',
        'points' => [
            ['name' => 'Eine Ansprechperson', 'text' => 'Technik-Fragen landen bei einem Menschen, der die Antwort kennt – oder sie schnell findet.'],
            ['name' => 'Hausmarke UniFi', 'text' => 'Mein Standard-Werkzeug. Cisco und die anderen üblichen Hersteller sind mir ebenso vertraut.'],
            ['name' => 'Für jede Größe', 'text' => 'Festivals, Produktionsfirmen und Firmen-Events – vom Zelt bis zur Halle.'],
            ['name' => 'Ton & IT aus einer Hand', 'text' => 'Wenn du willst, übernehme ich auch den FOH. Mehr dazu auf reichi.com.'],
        ],
        'facts' => [
            ['label' => 'Seit', 'value' => '2007'],
            ['label' => 'Basis', 'value' => 'Innviertel, OÖ'],
            ['label' => 'Einsatz', 'value' => 'Wo das Event ist'],
        ],
        'quote' => 'Good IT is boring IT – nobody notices it.',
    ],

    // ------------------------------------------------------------------
    // Referenz
    // ------------------------------------------------------------------
    'reference' => [
        'eyebrow' => 'Proof',
        'title' => 'Referenz',
        'items' => [
            [
                'name' => 'Woodstock der Blasmusik',
                'kind' => 'Festival · Innviertel',
                'text' => 'Event-IT für das Woodstock der Blasmusik im Innviertel.',
                'url' => 'https://www.woodstock.at/',
                'link_label' => 'woodstock.at',
            ],
        ],
        'more_label' => 'Bühnen-Referenzen seit 2007 stehen auf reichi.com',
        'more_url' => 'https://www.reichi.com/#portfolio',
    ],

    // ------------------------------------------------------------------
    // Anfrage / Kontakt
    // ------------------------------------------------------------------
    'hire' => [
        'id' => 'anfrage',
        'eyebrow' => 'Say hi',
        'title' => 'Erzähl mir von deinem Event.',
        'question' => 'Need Wi-Fi that survives a festival weekend? Let’s talk.',
        'paragraphs' => [
            'Egal ob du nur eine zweite Meinung zum Netzplan brauchst oder jemanden, der das Ganze aufbaut und am Wochenende betreut: Schreib mir ein paar Zeilen, ich melde mich.',
            'Je früher, desto entspannter – aber auch kurzfristig lässt sich meist etwas machen.',
        ],
        'form' => [
            'name' => 'Name',
            'email' => 'E-Mail-Adresse',
            'phone' => 'Telefonnummer',
            'phone_hint' => 'optional',
            'subject' => 'Worum geht’s?',
            'subject_placeholder' => 'Bitte wählen',
            'subject_options' => ['Aufbau', 'Betrieb vor Ort', 'Beratung', 'Sonstiges'],
            'message' => 'Nachricht',
            'message_hint' => 'Wann, wo, wie groß – und was soll funktionieren? Stichworte reichen.',
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
        ],
    ],

    // Website-spezifische Formulierungen in Impressum und Datenschutzerklärung
    'legal' => [
        'impressum_notes' => [
            'reichi.it ist die IT-Abteilung von reichi.com und wird vom selben Medieninhaber betrieben.',
        ],
        'form_section' => 'Anfrage',
        'external_examples' => 'reichi.com, Instagram, die Website des Festivals',
    ],

    'image_credits' => [],
];
