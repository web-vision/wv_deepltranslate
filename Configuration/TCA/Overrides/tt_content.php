<?php

if (!defined('TYPO3_MODE')) {
    die();
}

(static function (): void {
    // Feature Toggle
    // TODO remove if in v4
    if (TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Configuration\Features::class)
        ->isFeatureEnabled('deepltranslate.automaticTranslation')) {
        $GLOBALS['TCA']['tt_content']['ctrl']['deeplTranslation'] = true;
        $GLOBALS['TCA']['tt_content']['columns']['subheader']['l10n_mode'] = 'deepl';
        $GLOBALS['TCA']['tt_content']['columns']['header']['l10n_mode'] = 'deepl';
        $GLOBALS['TCA']['tt_content']['columns']['bodytext']['l10n_mode'] = 'deepl';
        return;
    }
    /**
     * @deprecated will be removed in v4
     */
    $GLOBALS['TCA']['tt_content']['columns']['subheader']['l10n_mode'] = 'prefixLangTitle';
})();
