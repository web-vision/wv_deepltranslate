<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Hooks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Hooks\TranslateHook;
use WebVision\WvDeepltranslate\Service\LanguageService;

/**
 * @covers \WebVision\WvDeepltranslate\Hooks\TranslateHook
 */
class TranslateHookTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/wv_deepltranslate',
    ];

    protected function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/Pages.xml');
        $this->setUpFrontendRootPage(
            1,
            [],
            [
                1 => 'EXT:wv_deepltranslate/Tests/Functional/Hooks/Fixtures/SiteConfig.yaml',
            ]
        );
    }

    /**
     * @test
     */
    public function contentTranslateWithDeepl(): void
    {
        $translateContent = 'Hello I would like to be translated';
        $expectedTranslation = 'Hallo, ich möchte gerne übersetzt werden';
        // @todo deepL api mockserver can only handle proton beam as translation, therefore use this.
        if (defined('DEEPL_MOCKSERVER_USED') && DEEPL_MOCKSERVER_USED === true) {
            $translateContent = 'proton beam';
            $expectedTranslation = 'Protonenstrahl';
        }

        $translateHook = GeneralUtility::makeInstance(TranslateHook::class);
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteConfig = $languageService->getCurrentSite('pages', 1);
        $sourceLanguageRecord = $languageService->getSourceLanguage($siteConfig['site']);
        $content = $translateHook->translateContent(
            $translateContent,
            [
                'uid' => 2,
                'language_isocode' => 'DE',
            ],
            'deepl',
            $sourceLanguageRecord
        );

        static::assertSame($expectedTranslation, $content);
    }

    /**
     * @test
     */
    public function contentNotTranslateWithDeeplWhenLanguageNotSupported(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/NotSupportedLanguage.xml');

        $translateHook = GeneralUtility::makeInstance(TranslateHook::class);

        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteConfig = $languageService->getCurrentSite('pages', 1);
        $sourceLanguageRecord = $languageService->getSourceLanguage($siteConfig['site']);
        $content = $translateHook->translateContent(
            'Hello I would like to be translated',
            [
                'uid' => 3, // This ist the LanguageID its was Configure in SiteConfig
                'title' => 'not supported language',
                'language_isocode' => 'BS',
            ],
            'deepl',
            $sourceLanguageRecord
        );

        static::assertSame('Hello I would like to be translated', $content);
    }

    /**
     * @test
     */
    public function translateContentElementsAndUpdatePagesProperties(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BeUsersTranslateDeeplFlag.xml');
        $this->setUpBackendUserFromFixture(2);

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $cmdMap = [
            'tt_content' => [
                1 => [
                    'localize' => 2,
                ],
            ],
            'localization' => [
                'custom' => [
                    'mode' => 'deepl',
                ],
            ],
        ];

        $dataHandler->start([], $cmdMap);
        $dataHandler->process_cmdmap();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $pageRow = $connection->select(
            [
                'uid',
                'tx_wvdeepltranslate_content_not_checked',
                'tx_wvdeepltranslate_translated_time',
            ],
            'pages',
            [
                'uid' => 2,
            ]
        )->fetchAssociative();

        static::assertArrayHasKey('tx_wvdeepltranslate_content_not_checked', $pageRow);
        static::assertSame(1, (int)$pageRow['tx_wvdeepltranslate_content_not_checked']);
        static::assertArrayHasKey('tx_wvdeepltranslate_translated_time', $pageRow);
        static::assertGreaterThan(0, (int)$pageRow['tx_wvdeepltranslate_translated_time']);
    }
}
