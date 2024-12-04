<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use WebVision\WvDeepltranslate\Exception\FileNotFoundException;
use WebVision\WvDeepltranslate\Service\ImportGlossaryEntryService;
use WebVision\WvDeepltranslate\Tests\Functional\AbstractDeepLTestCase;

final class ImportGlossaryEntryServiceTest extends AbstractDeepLTestCase
{
    protected function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../Fixtures/ExtensionConfig.php'
        );
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');

        parent::setUp();
    }

    /**
     * @test
     */
    public function getGlossaryEntriesFromCsvFileNotExists(): void
    {
        /** @var ImportGlossaryEntryService $importService */
        $importService = $this->get(ImportGlossaryEntryService::class);
        $importService->getGlossaryEntriesFromCsv('nonExisting.csv', ';', 1);

        static::expectException(FileNotFoundException::class);
        static::assertCount(0, $importService->getAllEntries());
    }

    /**
     * @test
     */
    public function getGlossaryEntriesFromCsv(): void
    {
        /** @var ImportGlossaryEntryService $importService */
        $importService = $this->get(ImportGlossaryEntryService::class);

        $entries = $importService->getGlossaryEntriesFromCsv(__DIR__ . '/../Fixtures/importEntries.csv', ',', 1);

        self::assertSame(
            [
                ['test one', 'test eins'],
                ['test two', 'test zwei'],
                ['test three', 'test drei'],
            ],
            $entries
        );
        static::assertCount(3, $importService->getAllEntries());
    }

    /**
     * @test
     */
    public function getGlossaryEntriesFromCsvAlreadyExists(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/entries.csv');

        /** @var ImportGlossaryEntryService $importService */
        $importService = $this->get(ImportGlossaryEntryService::class);
        $entries = $importService->getGlossaryEntriesFromCsv(__DIR__ . '/../Fixtures/importEntries.csv', ',', 1);

        self::assertSame([['test three', 'test drei']], $entries);
        static::assertCount(
            2,
            $importService->getFailedEntries(ImportGlossaryEntryService::ERROR_EXISTING)
        );
    }
}
