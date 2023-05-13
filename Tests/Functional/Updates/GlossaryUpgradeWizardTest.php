<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Upgrades\GlossaryUpgradeWizard;

/**
 * @covers \WebVision\WvDeepltranslate\Upgrades\GlossaryUpgradeWizard
 */
class GlossaryUpgradeWizardTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/wv_deepltranslate/Tests/Functional/Updates/Fixtures/Extension/test_extension',
        'web-vision/wv_deepltranslate',
    ];

    /**
     * @var array{non-empty-string, non-empty-string}
     */
    protected array $droppedTables = [];

    protected function tearDown(): void
    {
        if ($this->droppedTables !== []) {
            foreach ($this->droppedTables as $droppedTableName => $droppedTableCreateSQL) {
                $connection = $this->getConnectionPool()->getConnectionForTable($droppedTableName);
                foreach ($droppedTableCreateSQL as $sql) {
                    $connection->executeStatement($sql);
                }
            }
            $this->droppedTables = [];
        }
        parent::tearDown();
    }

    /**
     * @test
     */
    public function extensionsLoaded(): void
    {
        static::assertTrue(ExtensionManagementUtility::isLoaded('wv_deepltranslate'));
        static::assertTrue(ExtensionManagementUtility::isLoaded('test_extension'));
    }

    /**
     * @test
     */
    public function upgradeIsNotNecessaryBecauseTablasNotExist(): void
    {
        // Loaded test fixtures creates tables in the database, but this test expecteds that they are not existing.
        // So we need to drop them first.
        $this->dropTables(
            'tx_wvdeepltranslate_domain_model_glossaries',
            'tx_wvdeepltranslate_domain_model_glossariessync'
        );
        $wizard = GeneralUtility::makeInstance(GlossaryUpgradeWizard::class);

        $isNecessary = $wizard->updateNecessary();

        static::assertFalse($isNecessary);
    }

    /**
     * @test
     */
    public function upgradeIsNotNecessaryBecauseTablasIsEmpty(): void
    {
        $wizard = GeneralUtility::makeInstance(GlossaryUpgradeWizard::class);

        $isNecessary = $wizard->updateNecessary();

        static::assertFalse($isNecessary);
    }

    /**
     * @test
     */
    public function checkMigrateIsNecessaryResult(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tabletomigrate.csv');

        $conn = $this->getConnectionPool()->getConnectionByName('Default');
        $result = $conn->executeQuery('SELECT * FROM tx_wvdeepltranslate_glossary')->fetchAllAssociative();
        $detail = $conn->createSchemaManager()->listTableDetails('tx_wvdeepltranslate_glossary');

        $wizard = GeneralUtility::makeInstance(GlossaryUpgradeWizard::class);

        $isNecessary = $wizard->updateNecessary();

        static::assertTrue($isNecessary);
    }

    /**
     * @test
     */
    public function executeSuccessMigrationProcess(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tabletomigrate.csv');

        $wizard = GeneralUtility::makeInstance(GlossaryUpgradeWizard::class);

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock->expects(static::any())
            ->method('writeln');

        $wizard->setOutput($outputMock);

        $executeUpdate = $wizard->executeUpdate();

        static::assertTrue($executeUpdate, 'Upgrade process was failed');

        $entryQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->createQueryBuilder();
        $entryQueryBuilder->getRestrictions()->removeAll();
        $entryResult = $entryQueryBuilder->select('*')
            ->from('tx_wvdeepltranslate_glossaryentry')
            ->executeQuery();

        $entryRows = $entryResult->fetchAllAssociative();

        static::assertSame('Hello', $entryRows[0]['term']);
        static::assertSame(0, (int)$entryRows[0]['sys_language_uid']);

        static::assertSame('Welt', $entryRows[1]['term']);
        static::assertSame(1, (int)$entryRows[1]['sys_language_uid']);

        $glossaryQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->createQueryBuilder();
        $glossaryQueryBuilder->getRestrictions()->removeAll();
        $glossaryResult = $glossaryQueryBuilder
            ->select('*')
            ->from('tx_wvdeepltranslate_glossary')
            ->executeQuery();

        $glossaryRows = $glossaryResult->fetchAllAssociative();

        static::assertSame('64fdcdb4-a287-41b8-93df-47885c6b76ea', $glossaryRows[0]['glossary_id']);
        static::assertSame('en', $glossaryRows[0]['source_lang']);
        static::assertSame('de', $glossaryRows[0]['target_lang']);

        $beUserGroupQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->createQueryBuilder();
        $beUserGroupQueryBuilder->getRestrictions()->removeAll();
        $beUserGroup = $beUserGroupQueryBuilder
            ->select('*')
            ->from('be_groups')
            ->where(
                $beUserGroupQueryBuilder->expr()->eq('uid', 1)
            )->executeQuery();

        $beGroup = $beUserGroup->fetchAssociative();

        $tableSelect = explode(',', $beGroup['tables_select']);
        $tableModify = explode(',', $beGroup['tables_modify']);
        static::assertContains('tx_wvdeepltranslate_glossaryentry', $tableSelect);
        static::assertContains('tx_wvdeepltranslate_glossaryentry', $tableModify);
        static::assertContains('tx_wvdeepltranslate_glossary', $tableSelect);
        static::assertContains('tx_wvdeepltranslate_glossary', $tableModify);

        $pageQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->createQueryBuilder();
        $pageResult = $pageQueryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $pageQueryBuilder->expr()->eq('doktype', 254),
                $pageQueryBuilder->expr()->eq('uid', 1)
            )->executeQuery();

        $pageRow = $pageResult->fetchAssociative();

        static::assertSame('glossary', $pageRow['module']);
    }

    /**
     * @internal Test related method to drop tables.
     */
    private function dropTables(string ...$tables): void
    {
        $connection = $this->getConnectionPool()->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $schemaManager = $connection->createSchemaManager();
        foreach ($tables as $table) {
            $this->droppedTables[$table] = $connection->getDatabasePlatform()->getCreateTableSQL(
                $schemaManager->listTableDetails($table)
            );
            $schemaManager->dropTable($table);
        }
    }
}
