<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
        $settingsRepository->insertDeeplSettings(
            0,
            ['1' => 'de']
        );

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
        $settingsRepository->updateDeeplSettings(1, serialize(['1' => 'EN']));

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $settings = $connection->select(['*'], 'tx_deepl_settings', ['uid' => 1])->fetch();

        static::assertSame(serialize(['1' => 'EN']), $settings['languages_assigned']);
    }

    /** @test */
    public function checkSelectAssignments(): void
    {
        $settingsRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(SettingsRepository::class);
        $settings = $settingsRepository->getSettings();

        static::assertSame(1, $settings->getUid());
        static::assertSame(0, $settings->getPid());
        static::assertSame(['1' => 'de'], $settings->getLanguagesAssigned());
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
}
