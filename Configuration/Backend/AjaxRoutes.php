<?php
/**
 * Definitions for routes provided by EXT:deepl
 * Contains all AJAX-based routes for entry points
 *
 * Currently the "access" property is only used so no token creation + validation is made
 * but will be extended further.
 */
return [
    // Localize the records
    'records_localizedeepl' => [
        'path' => '/records/localizedeepl',
        'target' => WebVision\WvDeepltranslate\Override\LocalizationController::class . '::checkdeeplSettings',
    ],
];
