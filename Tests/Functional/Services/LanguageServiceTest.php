<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Exception\LanguageRecordNotFoundException;
use WebVision\WvDeepltranslate\Service\LanguageService;
use WebVision\WvDeepltranslate\Tests\Functional\Fixtures\Traits\SiteBasedTestTrait;

class LanguageServiceTest extends FunctionalTestCase
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
        ],
        'DE' => [
            'id' => 2,
            'title' => 'Deutsch',
            'locale' => 'de_DE',
            'iso' => 'de',
            'hrefLang' => 'de-DE',
            'direction' => '',
        ],
        'EB' => [
            'id' => 3,
            'title' => 'Britisch',
            'locale' => 'en_GB',
            'iso' => 'eb',
            'hrefLang' => 'en-GB',
            'direction' => '',
        ],
        'BS_default' => [
            'id' => 0,
            'title' => 'Bosnian',
            'locale' => 'bs_BA.utf8',
            'iso' => 'bs',
            'hrefLang' => 'bs',
            'direction' => '',
        ],
        'BS' => [
            'id' => 4,
            'title' => 'Bosnian',
            'locale' => 'bs_BA.utf8',
            'iso' => 'bs',
            'hrefLang' => 'bs',
            'direction' => '',
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

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Pages.csv');
        $this->writeSiteConfiguration(
            'site-a',
            $this->buildSiteConfiguration(1, '/', 'Home'),
            [
                $this->buildDefaultLanguageConfiguration('EN', '/'),
                $this->buildLanguageConfiguration('DE', '/de/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('EB', '/eb/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('BS', '/bs/', ['EN'], 'strict'),
            ]
        );
        $this->setUpFrontendRootPage(1, [], []);
        $this->writeSiteConfiguration(
            'site-b',
            $this->buildSiteConfiguration(3, '/', 'Home'),
            [
                $this->buildDefaultLanguageConfiguration('BS_default', '/bs/'),
                $this->buildLanguageConfiguration('DE', '/de/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('EB', '/eb/', ['EN'], 'strict'),
            ]
        );
        $this->setUpFrontendRootPage(3, [], []);
    }

    /**
     * @test
     */
    public function getCurrentSiteWithValidInformation(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        static::assertIsArray($siteInformation);
        static::assertArrayHasKey('site', $siteInformation);
        static::assertArrayHasKey('pageUid', $siteInformation);

        static::assertInstanceOf(Site::class, $siteInformation['site']);
        static::assertIsInt($siteInformation['pageUid']);
        static::assertSame(1, $siteInformation['pageUid']);
    }

    /**
     * @test
     */
    public function getCurrentSiteHasNoSite(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 2);

        static::assertNull($siteInformation);
    }

    /**
     * @test
     */
    public function getCurrentSiteByUsedContentId(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('tt_content', 3);

        static::assertIsArray($siteInformation);
        static::assertArrayHasKey('site', $siteInformation);
        static::assertArrayHasKey('pageUid', $siteInformation);

        static::assertInstanceOf(Site::class, $siteInformation['site']);
        static::assertIsInt($siteInformation['pageUid']);
        static::assertSame(1, $siteInformation['pageUid']);
    }

    /**
     * @test
     */
    public function getSourceLanguageInformationIsValid(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        $sourceLanguageRecord = $languageService->getSourceLanguage($siteInformation['site']);

        static::assertArrayHasKey('uid', $sourceLanguageRecord);
        static::assertArrayHasKey('title', $sourceLanguageRecord);
        static::assertArrayHasKey('language_isocode', $sourceLanguageRecord);

        static::assertSame(0, $sourceLanguageRecord['uid']);
        static::assertSame('EN', $sourceLanguageRecord['language_isocode']);
    }

    /**
     * @test
     */
    public function setAutoDetectOptionForSourceLanguageNotSupported(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 3);
        $sourceLanguageRecord = $languageService->getSourceLanguage($siteInformation['site']);

        static::assertContains('auto', $sourceLanguageRecord);
    }

    /**
     * @test
     */
    public function getTargetLanguageInformationIsValid(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        $sourceLanguageRecord = $languageService->getTargetLanguage($siteInformation['site'], 2);

        static::assertArrayHasKey('uid', $sourceLanguageRecord);
        static::assertArrayHasKey('title', $sourceLanguageRecord);
        static::assertArrayHasKey('language_isocode', $sourceLanguageRecord);

        static::assertSame(2, $sourceLanguageRecord['uid']);
        static::assertSame('DE', $sourceLanguageRecord['language_isocode']);
    }

    /**
     * @test
     */
    public function getTargetLanguageExceptionWhenLanguageNotExist(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        static::expectException(LanguageRecordNotFoundException::class);
        static::expectExceptionMessage('Language "1" not found in SiteConfig "Home"');
        $languageService->getTargetLanguage($siteInformation['site'], 1);
    }

    /**
     * @test
     */
    public function getTargetLanguageExceptionWhenLanguageIsoNotSupported(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        static::expectException(LanguageIsoCodeNotFoundException::class);
        static::expectExceptionMessage('No API supported target found for language "Bosnian" in site "Home"');
        $languageService->getTargetLanguage($siteInformation['site'], 4);
    }
}
