<?php

return [

    'mail' => [

        'subject' => '🔥 Attaque possible sur :domain',
        'message' => 'Une possible attaque :middleware sur :domain a été détectée à partir de :ip address. L\'URL suivante a été affectée: :url',

    ],

    'slack' => [

        'message' => 'Une éventuelle attaque sur :domain a été détectée.',

    ],

];
