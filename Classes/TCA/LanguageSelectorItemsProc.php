<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\TCA;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class LanguageSelectorItemsProc
{
    public function getFieldsForTarget(array &$configuration): void
    {
        $glossaryService = GeneralUtility::makeInstance(DeeplGlossaryService::class);

        $possibleGlossaryConfig = $glossaryService->getPossibleGlossaryLanguageConfig();

        if (!isset($configuration['row']['source_lang'])) {
            return;
        }

        $possibleSources = ($possibleGlossaryConfig[$configuration['row']['source_lang']] ?? null);

        if ($possibleSources === null) {
            return;
        }

        foreach ($possibleSources as $possibleSource) {
            $label = LocalizationUtility::translate(
                sprintf('LLL:EXT:core/Resources/Private/Language/db.xlf:sys_language.language_isocode.%s', $possibleSource)
            ) ?? $possibleSource;
            $configuration['items'][] = [$label, $possibleSource];
        }
    }

    public function getFieldsForSource(array &$configuration): void
    {
        $glossaryService = GeneralUtility::makeInstance(DeeplGlossaryService::class);

        $possibleGlossaryConfig = $glossaryService->getPossibleGlossaryLanguageConfig();

        $possibleSources = array_keys($possibleGlossaryConfig);

        foreach ($possibleSources as $possibleSource) {
            $label = LocalizationUtility::translate(
                sprintf('LLL:EXT:core/Resources/Private/Language/db.xlf:sys_language.language_isocode.%s', $possibleSource)
            ) ?? $possibleSource;
            $configuration['items'][] = [$label, $possibleSource];
        }
    }

    public function glossaryLabel(&$parameters): void
    {
        $glossary = BackendUtility::getRecord(
            $parameters['table'],
            $parameters['row']['uid'],
            'entries,glossary_id,glossary_lastsync,tstamp,rowDescription'
        );

        if ($glossary === null) {
            return;
        }

        $localizationString = 'glossary.title.count';
        if (isset($glossary['entries']) && (int)$glossary['entries'] === 1) {
            $localizationString = 'glossary.title.count.single';
        }

        $duplicates = false;
        if ($glossary['rowDescription'] !== '') {
            $duplicates = true;
        }

        $isSync = false;
        if (
            $glossary['glossary_id'] != ''
            && $glossary['tstamp'] < $glossary['glossary_lastsync']
        ) {
            $isSync = true;
        }

        $parameters['title'] = sprintf(
            '%s%s (%d %s) [%s]',
            $duplicates ? LocalizationUtility::translate(
                'glossary.title.duplicates',
                'wv_deepltranslate'
            ) : '',
            $parameters['row']['glossary_name'],
            (int)$glossary['entries'] ?? 0,
            LocalizationUtility::translate(
                $localizationString,
                'wv_deepltranslate'
            ),
            LocalizationUtility::translate(
                $isSync ? 'glossary.title.sync.true' : 'glossary.title.sync.false',
                'wv_deepltranslate'
            )
        );
    }
}
