<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\CsvUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryEntryRepository;
use WebVision\WvDeepltranslate\Exception\FileNotFoundException;

final class ImportGlossaryEntryService
{
    public const ERROR_INVALID = 'invalid';
    public const ERROR_EXISTING = 'existing';
    public const ERROR_LOCALIZATION = 'localization';

    protected ResourceFactory $resourceFactory;
    protected GlossaryEntryRepository $glossaryEntryRepository;
    protected DataHandler $dataHandler;

    protected array $allEntries = [];
    protected array $failedEntries = [
        self::ERROR_INVALID => [],
        self::ERROR_EXISTING => [],
        self::ERROR_LOCALIZATION => [],
    ];

    public function __construct(
        ResourceFactory $resourceFactory,
        GlossaryEntryRepository $glossaryEntryRepository,
        DataHandler $dataHandler
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->glossaryEntryRepository = $glossaryEntryRepository;
        $this->dataHandler = $dataHandler;
    }

    /**
     * @throws FileNotFoundException
     * @return array<mixed>
     */
    public function getGlossaryEntriesFromCsv(string $filePath, string $separator, int $pageId): array
    {
        $fileContent = $this->getFileContents($filePath);

        if ($fileContent === null) {
            throw new FileNotFoundException('Csv file not found identifier: ' . $filePath, 1729245627);
        }

        $this->allEntries = CsvUtility::csvToArray($fileContent, $separator, '"', 2);

        return $this->getValidatedEntries($pageId);
    }

    /**
     * @param int $pageId
     * @return array<mixed>
     */
    protected function getValidatedEntries(int $pageId): array
    {
        $entries = [];

        foreach ($this->allEntries as $index => $entry) {
            $csvColumn = $index + 1;
            $originalEntry = (string)($entry[0] ?? '');
            $localizedEntry = (string)($entry[1] ?? '');

            if (count($entry) !== 2 || $originalEntry === '' || $localizedEntry === '') {
                $this->failedEntries['invalid'][$csvColumn] = $originalEntry;
                continue;
            }

            if (
                $this->entryExists($originalEntry, $pageId) === true ||
                $this->entryExists($localizedEntry, $pageId) === true
            ) {
                $this->failedEntries['existing'][$csvColumn] = $originalEntry;
                continue;
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * @param array<mixed> $entries
     * @param int $pageId
     * @param int $sysLanguageUid
     * @return void
     */
    public function insertEntriesLocal(array $entries, int $pageId, int $sysLanguageUid): void
    {
        $mappedEntries = $this->insertOriginalEntries($entries, $pageId);
        $this->insertLocalizedEntries($mappedEntries, $pageId, $sysLanguageUid);
    }

    /**
     * @param array<mixed> $entries
     * @param int $pageId
     * @return array<mixed>
     */
    protected function insertOriginalEntries(array $entries, int $pageId): array
    {
        $mappedEntries = [];
        $data = [
            'tx_wvdeepltranslate_glossaryentry' => [],
        ];

        foreach ($entries as $index => $entry) {
            $originalNewId = 'NEW_original_' . $index;
            $originalEntry = (string)($entry[0] ?? '');
            $mappedEntries[$originalNewId] = $entry;

            $data['tx_wvdeepltranslate_glossaryentry'][$originalNewId] = [
                'pid' => $pageId,
                'term' => $originalEntry,
            ];
        }

        $this->dataHandler->start($data, []);
        $this->dataHandler->process_datamap();

        return $mappedEntries;
    }

    /**
     * @param array<mixed> $mappedEntries
     * @param int $pageId
     * @param int $sysLanguageUid
     * @return void
     */
    protected function insertLocalizedEntries(array $mappedEntries, int $pageId, int $sysLanguageUid): void
    {
        $localizedData = [
            'tx_wvdeepltranslate_glossaryentry' => [],
        ];

        foreach ($mappedEntries as $identifier => $entry) {
            $uid = (int) ($this->dataHandler->substNEWwithIDs[$identifier] ?? 0);
            $originalEntry = (string)($entry[0] ?? '');
            $localizedEntry = (string)($entry[1] ?? '');

            if (
                $uid === 0 ||
                $this->dataHandler->doesRecordExist(
                    'tx_wvdeepltranslate_glossaryentry',
                    $uid,
                    Permission::ALL
                ) === false
            ) {
                $this->failedEntries['localization'][] = $originalEntry;
                continue;
            }

            $localizedData['tx_wvdeepltranslate_glossaryentry'][$identifier . '_localized'] = [
                'l10n_parent' => $uid,
                'sys_language_uid' => $sysLanguageUid,
                'pid' => $pageId,
                'term' => $localizedEntry,
            ];
        }

        $this->dataHandler->start($localizedData, []);
        $this->dataHandler->process_datamap();
    }

    public function entryExists(string $term, int $pageId): bool
    {
        return $this->glossaryEntryRepository->findBy(['pid' => $pageId, 'term' => $term]) !== null;
    }

    protected function getFileContents(string $csvFilePath): ?string
    {
        if (file_exists($csvFilePath)) {
            $fileContents = file_get_contents($csvFilePath, true);
            return $fileContents !== false ? $fileContents : null;
        }

        return null;
    }

    /**
     * @return array<mixed>
     */
    public function getAllEntries(): array
    {
        return $this->allEntries;
    }

    /**
     * @param string $key
     * @return array<string>
     */
    public function getFailedEntries(string $key): array
    {
        return $this->failedEntries[$key] ?? [];
    }

    public function getFailuresCount(): int
    {
        return count($this->failedEntries[self::ERROR_INVALID]) +
            count($this->failedEntries[self::ERROR_EXISTING]) +
            count($this->failedEntries[self::ERROR_LOCALIZATION]);
    }

    /**
     * @return array<string>
     */
    public function getDataHandlerErrors(): array
    {
        return $this->dataHandler->errorLog;
    }
}
