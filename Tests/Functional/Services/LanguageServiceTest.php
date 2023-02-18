<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Service\LanguageService;

class LanguageServiceTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();

        $this->setUpFrontendRootPage(1);
        $this->setUpSites(1, [
            'EXT:wv_deepltranslate/Tests/Functional/Services/Fixtures/SiteConfig.yaml',
        ]);
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

        $sourceLanguageRecord = $languageService->getSourceLanguage($siteInformation, 0);

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
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        $sourceLanguageRecord = $languageService->getSourceLanguage($siteInformation, 5);

        static::expectException(LanguageIsoCodeNotFoundException::class);
    }
}
