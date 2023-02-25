<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services\v10;

use Nimut\TestingFramework\v10\TestCase\FunctionalTestCase;
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
            require __DIR__ . '/../../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Language.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Settings.xml');

        $this->setUpFrontendRootPage(
            1,
            [],
            [
                1 => 'EXT:wv_deepltranslate/Tests/Functional/Services/Fixtures/SiteConfig.yaml',
            ]
        );
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
        $this->typo3VersionSkip();

        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        static::expectException(LanguageRecordNotFoundException::class);
        static::expectExceptionMessage('No language for record with uid "5" found.');
        $sourceLanguageRecord = $languageService->getTargetLanguage($siteInformation['site'], 5);
    }

    /**
     * @test
     */
    public function getTargetLanguageExceptionWhenLanguageIsoNotSupported(): void
    {
        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $siteInformation = $languageService->getCurrentSite('pages', 1);

        static::expectException(LanguageIsoCodeNotFoundException::class);
        static::expectExceptionMessage('No API supported target found for language "Bosnian"');
        $sourceLanguageRecord = $languageService->getTargetLanguage($siteInformation['site'], 4);
    }

    private function typo3VersionSkip(): void
    {
        $typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
            \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
        );
        if (version_compare((string)$typo3VersionArray['version_main'], '11', '=')) {
            static::markTestSkipped('Skip test, can only use in version 10');
        }
    }
}
