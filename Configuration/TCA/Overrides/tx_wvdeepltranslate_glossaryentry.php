<?php

defined('TYPO3') or die();

(static function (): void {
    if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12) {
        // Adjust TCA configuration to be TYPO3 v12 compatible avoiding TCA automigration.
        // 1.   https://review.typo3.org/c/Packages/TYPO3.CMS/+/77626
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.3/Deprecation-99739-IndexedArrayKeysForTCAItems.html
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.3/Feature-99739-AssociativeArrayKeysForTCAItems.html
        //      => Directly solved in `Configuration/TCA/tx_wvdeepltranslate_glossaryentry.php as version check.
        // 2.   https://review.typo3.org/c/Packages/TYPO3.CMS/+/73709
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97035-RequiredOptionInEvalKeyword.html
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97035-UtilizeRequiredDirectlyInTCAFieldConfiguration.html
        // 3.   https://review.typo3.org/c/Packages/TYPO3.CMS/+/75123
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-98024-TCA-option-cruserid-removed.html
        // 4.   https://review.typo3.org/c/Packages/TYPO3.CMS/+/74027
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97232-NewTCATypeDatetime.html
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-97358-RemovedEvalintFromTCATypeDatetime.html
        //      => not needed for this table (yet)

        // required eval -> flag [2]
        $GLOBALS['TCA']['tx_wvdeepltranslate_glossaryentry']['columns']['term']['config']['required'] = true;
        unset($GLOBALS['TCA']['tx_wvdeepltranslate_glossaryentry']['columns']['term']['config']['eval']);

        // removed cruser_id [3]
        unset($GLOBALS['TCA']['tx_wvdeepltranslate_glossaryentry']['ctrl']['cruser_id']);
    }
})();
