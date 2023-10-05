<?php

return [
    'languages' => [
        'en' => 'english',
        'fr' => 'french',
        'es' => 'spanish',
        'de' => 'german',
        'jp' => 'japanese',
    ],
    'override' => [
        'fr' => [
            'lang' => [
                'locales' => "['fr_FR', 'fr_FR.utf8', 'fr', 'French']",
                'SimpleDefine' => [
                    'BOX_INFORMATION_SITE_MAP' => 'Plan du site',
                    'FOOTER_TEXT_BODY' => "droits d\'auteur &copy; ' . date('Y') . ' <a href=\"' . zen_href_link(FILENAME_DEFAULT) . '\">' . STORE_NAME . '</a>. alimenté par <a href=\"https://www.zen-cart.com\" rel=\"noopener noreferrer\" target=\"_blank\">Zen Cart</a>",
                ]
            ],
        ],
    ],
];

