<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\CsvUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryEntryRepository;
use WebVision\WvDeepltranslate\Exception\FileNotFoundException;

final class ImportGlossaryEntryService
{
    protected ResourceFactory $resourceFactory;
    protected GlossaryEntryRepository $glossaryEntryRepository;

    public function __construct(ResourceFactory $resourceFactory, GlossaryEntryRepository $glossaryEntryRepository)
    {
        $this->resourceFactory = $resourceFactory;
        $this->glossaryEntryRepository = $glossaryEntryRepository;
    }

    /**
     * @throws FileNotFoundException
     * @return array<mixed>
     */
    public function getGlossaryEntriesFromCsv(string $filePath, string $separator): array
    {
        $file = $this->getFile($filePath);

        if ($file === null) {
            throw new FileNotFoundException('Csv file not found identifier: ' . $filePath, 1729245627);
        }

        return CsvUtility::csvToArray($file->getContents(), $separator, '"', 2);
    }

    /**
     * @param array<mixed> $entries
     * @param int $pageId
     * @param int $sysLanguageUid
     * @return void
     */
    public function insertEntriesLocal(array $entries, int $pageId, int $sysLanguageUid): void
    {
        foreach ($entries as $entry) {
            $originalUid = $this->glossaryEntryRepository->add([
                'pid' => $pageId,
                'term' => $entry[0],
            ]);

            if ($originalUid > 0) {
                $this->glossaryEntryRepository->add([
                    'l10n_parent' => $originalUid,
                    'sys_language_uid' => $sysLanguageUid,
                    'pid' => $pageId,
                    'term' => $entry[1],
                ]);
            }
        }
    }

    protected function getFile(string $csvFilePath): ?File
    {
        $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($csvFilePath);
        return $file instanceof ProcessedFile ? $file->getOriginalFile() : $file;
    }
}
