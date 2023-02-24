<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class DuplicateEntryDetectionHook
{
    private static bool $isGlossary = false;

    private static int $glossaryCount = 0;

    private static int $current = 0;

    private static int $glossaryId;

    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        // We're only interested in glossaries
        if (
            $table !== 'tx_wvdeepltranslate_glossary'
            && $table !== 'tx_wvdeepltranslate_glossaryentry'
        ) {
            return;
        }
        // not set, a glossary updated
        // count glossary and entries to detect,
        // when last item written to database
        if (!self::$isGlossary) {
            self::$glossaryCount =
                count($dataHandler->datamap['tx_wvdeepltranslate_glossary']) +
                count($dataHandler->datamap['tx_wvdeepltranslate_glossaryentry'] ?? []);
            self::$isGlossary = true;
        }
        if ($table === 'tx_wvdeepltranslate_glossary') {
            if (!MathUtility::canBeInterpretedAsInteger($id)) {
                $id = $dataHandler->substNEWwithIDs[$id];
            }
            self::$glossaryId = $id;
        }
        self::$current++;

        // we detect last item, then do update,
        // otherwise return
        if (self::$current < self::$glossaryCount) {
            return;
        }
        $repository = GeneralUtility::makeInstance(GlossaryRepository::class);
        $glossary = $repository->getGlossaryInformationForSync(self::$glossaryId);
        if (count($glossary['entries']) === 0) {
            $this->updateRowDescription();
            return;
        }
        $duplicatedEntries = DeeplGlossaryService::detectDuplicateSourceValues($glossary['entries']);
        if (count($duplicatedEntries) === 0) {
            $this->updateRowDescription();
            return;
        }
        $descriptionRow = 'Duplicates:' . PHP_EOL;

        $detectedDuplicates = [];
        foreach ($duplicatedEntries as $entry) {
            $detectedDuplicates[] = sprintf('%s -> %s', $entry['source'], $entry['target']);
        }

        $descriptionRow .= PHP_EOL . implode(PHP_EOL, $detectedDuplicates);
        $this->updateRowDescription($descriptionRow);
    }

    private function updateRowDescription(string $description = ''): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary')
            ->update(
                'tx_wvdeepltranslate_glossary',
                [
                    'rowDescription' => $description,
                ],
                [
                    'uid' => self::$glossaryId,
                ]
            );
    }
}
