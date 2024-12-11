<?php

use WebVision\Deepltranslate\Core\Controller\GlossarySyncController;

return [
    'glossaryupdate' => [
        'path' => '/glossary',
        'target' => GlossarySyncController::class . '::update',
    ],
];
