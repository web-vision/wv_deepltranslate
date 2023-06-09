<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Hooks;

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Hooks\TranslateHook;
use WebVision\WvDeepltranslate\Service\LanguageService;
use WebVision\WvDeepltranslate\Tests\Functional\Fixtures\Traits\SiteBasedTestTrait;

/**
 * @covers \WebVision\WvDeepltranslate\Hooks\TranslateHook
 */
final class TranslateHookTest extends FunctionalTestCase
{
    use SiteBasedTestTrait;
    protected const LANGUAGE_PRESETS = [
        'EN' => [
            'id' => 0,
            'title' => 'English',
            'locale' => 'en_US.UTF-8',
            'iso' => 'en',
            'hrefLang' => 'en-US',
            'direction' => '',
            'custom' => [
                'deeplTargetLanguage' => '',
            ],
        ],
        'DE' => [
            'id' => 2,
            'title' => 'Deutsch',
            'locale' => 'de_DE',
            'iso' => 'de',
            'hrefLang' => 'de-DE',
            'direction' => '',
            'custom' => [
                'deeplTargetLanguage' => 'DE',
            ],
        ],
        'EB' => [
            'id' => 3,
            'title' => 'Britisch',
            'locale' => 'en_GB',
            'iso' => 'eb',
            'hrefLang' => 'en-GB',
            'direction' => '',
            'custom' => [
                'deeplTargetLanguage' => 'EN-GB',
            ],
        ],
        'BS' => [
            'id' => 4,
            'title' => 'Bosnian',
            'locale' => 'bs_BA.utf8',
            'iso' => 'bs',
            'hrefLang' => 'bs',
            'direction' => '',
            'custom' => [
                'deeplTargetLanguage' => '',
            ],
        ],
    ];

    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'web-vision/wv_deepltranslate',
    ];

    protected function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->writeSiteConfiguration(
            'acme',
            $this->buildSiteConfiguration(1, '/', 'Home'),
            [
                $this->buildDefaultLanguageConfiguration('EN', '/'),
                $this->buildLanguageConfiguration('EB', '/eb/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('DE', '/de/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('BS', '/bs/', ['EN'], 'strict'),
            ]
        );
        $this->setUpFrontendRootPage(1, [], []);
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
        $serverParams = array_replace($_SERVER, ['HTTP_HOST' => 'example.com', 'SCRIPT_NAME' => '/typo3/index.php']);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('http://example.com/typo3/index.php', 'GET', null, $serverParams))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($serverParams));

        $translateHook = GeneralUtility::makeInstance(TranslateHook::class);
        $languageService = $this->get(LanguageService::class);
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
        $serverParams = array_replace($_SERVER, ['HTTP_HOST' => 'example.com', 'SCRIPT_NAME' => '/typo3/index.php']);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('http://example.com/typo3/index.php', 'GET', null, $serverParams))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($serverParams));

        $translateHook = GeneralUtility::makeInstance(TranslateHook::class);

        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteConfig = $languageService->getCurrentSite('pages', 1);
        $sourceLanguageRecord = $languageService->getSourceLanguage($siteConfig['site']);
        $content = $translateHook->translateContent(
            'Hello I would like to be translated',
            [
                'uid' => 4, // This is the LanguageID its was Configure in SiteConfig
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/BeUsersTranslateDeeplFlag.csv');
        $this->setUpBackendUser(2);
        Bootstrap::initializeLanguageObject();

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
