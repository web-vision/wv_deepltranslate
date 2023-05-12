<?php

if (!defined('TYPO3_MODE')) {
    die();
}

(static function (): void {
    $GLOBALS['TCA']['tt_content']['columns']['subheader']['l10n_mode'] = 'prefixLangTitle';
})();
