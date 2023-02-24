<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\TCA;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class EntryItemProcFunc
{
    public function entryLabel(&$parameters): void
    {
        $entry = BackendUtility::getRecord(
            $parameters['table'],
            $parameters['row']['uid'],
            'source,target,glossary'
        );
        // build default title
        $parameters['title'] = sprintf(
            '%s => %s',
            $entry['source'],
            $entry['target']
        );

        $glossary = BackendUtility::getRecord(
            'tx_wvdeepltranslate_glossary',
            $entry['glossary'],
            'uid'
        );
        $glossaryForSync = GeneralUtility::makeInstance(GlossaryRepository::class)
            ->getGlossaryInformationForSync($glossary['uid']);
        $duplicateEntries = DeeplGlossaryService::detectDuplicateSourceValues($glossaryForSync['entries']);

        if (count($duplicateEntries) === 0) {
            return;
        }
        $duplicate = false;
        foreach ($duplicateEntries as $duplicateEntry) {
            if ($entry['source'] === $duplicateEntry['source']) {
                $duplicate = true;
                break;
            }
        }

        if ($duplicate) {
            $parameters['title'] = '[DUPLICATE] ' . $parameters['title'];
        }
    }
}
