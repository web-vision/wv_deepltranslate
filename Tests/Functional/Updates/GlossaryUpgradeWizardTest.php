<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Updates;

use Doctrine\DBAL\Driver\Statement;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Upgrades\GlossaryUpgradeWizard;

/**
 * @covers \WebVision\WvDeepltranslate\Upgrades\GlossaryUpgradeWizard
 */
class GlossaryUpgradeWizardTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/wv_deepltranslate/Tests/Functional/Updates/Fixtures/Extension/test_extension',
        'typo3conf/ext/wv_deepltranslate',
    ];

    /**
     * @test
     */
    public function upgradeIsNotNecessaryBecauseTablasNotExist(): void
    {
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
        $this->importDataSet(__DIR__ . '/Fixtures/TableToMigrate.xml');

        $wizard = GeneralUtility::makeInstance(GlossaryUpgradeWizard::class);

        $isNecessary = $wizard->updateNecessary();

        static::assertTrue($isNecessary);
    }

    /**
     * @test
     */
    public function executeSuccessMigrationProcess(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TableToMigrate.xml');

        $wizard = GeneralUtility::makeInstance(GlossaryUpgradeWizard::class);

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock->expects(static::any())
            ->method('writeln');

        $wizard->setOutput($outputMock);

        $executeUpdate = $wizard->executeUpdate();

        static::assertTrue($executeUpdate, 'Upgrade process was failed');

        $entryQueryBuilder = $this->getDatabaseConnection()->getDatabaseInstance();
        $entryQueryBuilder->getRestrictions()->removeAll();
        /** @var Statement<array> $entryResult */
        $entryResult = $entryQueryBuilder->select('*')
            ->from('tx_wvdeepltranslate_glossaryentry')
            ->execute();

        $entryRows = $entryResult->fetchAll();

        static::assertSame('Hello', $entryRows[0]['term']);
        static::assertSame(0, (int)$entryRows[0]['sys_language_uid']);

        static::assertSame('Welt', $entryRows[1]['term']);
        static::assertSame(1, (int)$entryRows[1]['sys_language_uid']);

        $glossaryQueryBuilder = $this->getDatabaseConnection()->getDatabaseInstance();
        $glossaryQueryBuilder->getRestrictions()->removeAll();
        /** @var Statement<array> $glossaryResult */
        $glossaryResult = $glossaryQueryBuilder
            ->select('*')
            ->from('tx_wvdeepltranslate_glossary')
            ->execute();

        $glossaryRows = $glossaryResult->fetchAll();

        static::assertSame('64fdcdb4-a287-41b8-93df-47885c6b76ea', $glossaryRows[0]['glossary_id']);
        static::assertSame('en', $glossaryRows[0]['source_lang']);
        static::assertSame('de', $glossaryRows[0]['target_lang']);

        $beUserGroupQueryBuilder = $this->getDatabaseConnection()->getDatabaseInstance();
        $beUserGroupQueryBuilder->getRestrictions()->removeAll();
        /** @var Statement<array> $beUserGroup */
        $beUserGroup = $beUserGroupQueryBuilder->select('*')
            ->from('be_groups')
            ->where(
                $beUserGroupQueryBuilder->expr()->eq('uid', 1)
            )->execute();

        $beGroup = $beUserGroup->fetch();

        static::assertContains('tx_wvdeepltranslate_glossaryentry', explode(',', $beGroup['tables_select']));
        static::assertContains('tx_wvdeepltranslate_glossaryentry', explode(',', $beGroup['tables_modify']));
        static::assertContains('tx_wvdeepltranslate_glossary', explode(',', $beGroup['tables_select']));
        static::assertContains('tx_wvdeepltranslate_glossary', explode(',', $beGroup['tables_modify']));
    }
}
