<?php
/**
 * Definitions for routes provided by EXT:deepl
 * Contains all AJAX-based routes for entry points
 *
 * Currently, the "access" property is only used so no token creation + validation is made
 * but will be extended further.
 */

return [
    'deepl_check_configuration' => [
        'path' => '/deepl/check-configuration',
        'target' => WebVision\WvDeepltranslate\Controller\Backend\AjaxController::class . '::checkExtensionConfiguration',
    ],
];
