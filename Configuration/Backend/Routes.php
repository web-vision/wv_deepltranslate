<?php

use WebVision\WvDeepltranslate\Controller\GlossarySyncController;

return [
    'glossaryupdate' => [
        'path' => '/glossary',
        'target' => GlossarySyncController::class . '::update',
    ],
];
