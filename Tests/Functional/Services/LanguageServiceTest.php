<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Exception\LanguageRecordNotFoundException;
use WebVision\WvDeepltranslate\Service\LanguageService;

class LanguageServiceTest extends FunctionalTestCase
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

        $this->importDataSet(__DIR__ . '/Fixtures/Pages.xml');

        $this->setUpFrontendRootPage(
            1,
            [],
            [
                1 => 'EXT:wv_deepltranslate/Tests/Functional/Services/Fixtures/SiteConfig.yaml',
            ]
        );
        $this->setUpFrontendRootPage(
            3,
            [],
            [
                3 => 'EXT:wv_deepltranslate/Tests/Functional/Services/Fixtures/SiteConfigEnNotDefault.yaml',
            ]
        );
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
    public function getSourceLanguageExceptionWhenLanguageNotExist(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 3);

        static::expectException(LanguageIsoCodeNotFoundException::class);
        static::expectExceptionMessage('No API supported target found for language "Bosnian"', );
        $sourceLanguageRecord = $languageService->getSourceLanguage($siteInformation['site']);
    }

    /**
     * @test
     */
    public function getTargetLanguageInformationIsValid(): void
    {
        $this->typo3VersionSkip();

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
        $this->typo3VersionSkip();

        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        static::expectException(LanguageRecordNotFoundException::class);
        static::expectExceptionMessage('Language "1" not found in SiteConfig "Home"');
        $sourceLanguageRecord = $languageService->getTargetLanguage($siteInformation['site'], 1);
    }

    /**
     * @test
     */
    public function getTargetLanguageExceptionWhenLanguageIsoNotSupported(): void
    {
        $this->typo3VersionSkip();

        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        static::expectException(LanguageIsoCodeNotFoundException::class);
        static::expectExceptionMessage('No API supported target found for language "Bosnian" in site "Home"');
        $sourceLanguageRecord = $languageService->getTargetLanguage($siteInformation['site'], 4);
    }

    private function typo3VersionSkip(): void
    {
        $typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
            \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
        );
        if (version_compare((string)$typo3VersionArray['version_main'], '11', '<')) {
            static::markTestSkipped('Skip test, can only use in version 11');
        }
    }
}
