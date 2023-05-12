<?php

defined('TYPO3') or die();

(static function (): void {
    $GLOBALS['TCA']['tt_content']['columns']['subheader']['l10n_mode'] = 'prefixLangTitle';
})();
