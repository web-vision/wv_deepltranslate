<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;

class SettingsRepositoryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/wv_deepltranslate',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../../Fixtures/Settings.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/Language.xml');
    }

    /** @test */
    public function insertSettingsRecord(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $settingsRepository->insertDeeplSettings([
            'uid' => 2,
            'pid' => 0,
            'languages_assigned' => serialize(['1' => 'de']),
        ]);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $settings = $connection->select(['*'], 'tx_deepl_settings', ['uid' => 2])->fetch();

        static::assertSame(2, $settings['uid']);
        static::assertSame(0, $settings['pid']);
        static::assertSame(serialize(['1' => 'de']), $settings['languages_assigned']);
    }

    /** @test */
    public function updateSettingsRecord(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $settingsRepository->updateDeeplSettings([
            'uid' => 1,
            'languages_assigned' => serialize(['1' => 'EN']),
        ]);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $settings = $connection->select(['*'], 'tx_deepl_settings', ['uid' => 1])->fetch();

        static::assertSame(serialize(['1' => 'EN']), $settings['languages_assigned']);
    }

    /** @test */
    public function checkSelectAssignments(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $settings = $settingsRepository->getAssignments();

        static::assertArrayHasKey('uid', $settings);
        static::assertSame(1, $settings['uid']);
        static::assertArrayHasKey('pid', $settings);
        static::assertSame(0, $settings['pid']);
        static::assertArrayHasKey('languages_assigned', $settings);
        static::assertSame(serialize(['1' => 'de']), $settings['languages_assigned']);
    }

    /** @test */
    public function findMappingsByUid(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $mapping = $settingsRepository->getMappings(1);

        static::assertSame('de', $mapping);
    }

    /** @test */
    public function getApiSupportedLanguageWhenDatabaseConfigurationEmpty(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $inputArray = [
            'hallo' => 'welt',
        ];
        $apiSupportedLanguages = $settingsRepository->getSupportedLanguages($inputArray);

        static::assertArrayHasKey('hallo', $apiSupportedLanguages);
        static::assertContains('welt', $apiSupportedLanguages);
    }

    /** @test */
    public function getApiSupportedLanguage(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $inputArray = [
            'hallo' => 'welt',
        ];
        $apiSupportedLanguages = $settingsRepository->getSupportedLanguages($inputArray);

        static::assertArrayHasKey('hallo', $apiSupportedLanguages);
        static::assertContains('welt', $apiSupportedLanguages);
        static::assertContains('de', $apiSupportedLanguages);
    }

    /** @test */
    public function findSysLanguages(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);

        $languages = $settingsRepository->getSysLanguages();

        static::assertContains('de', $languages[0]);
    }

    /** @test */
    public function getRecords(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $record = $settingsRepository->getRecordField('sys_language', ['*'], 1);
        static::assertIsArray($record);
    }
}
