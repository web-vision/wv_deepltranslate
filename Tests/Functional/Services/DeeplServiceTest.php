<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Service\DeeplService;

/**
 * @covers \WebVision\WvDeepltranslate\Service\DeeplService
 */
class DeeplServiceTest extends FunctionalTestCase
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

        $this->importDataSet(__DIR__ . '/../Fixtures/Settings.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/Language.xml');
    }

    /**
     * @test
     */
    public function translateContentFromDeToEn(): void
    {
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            'Ich möchte gern übersetzt werden!',
            'EN',
            'DE',
            ''
        );

        static::assertSame('I would like to be translated!', $responseObject->translations[0]->text);
    }

    /**
     * @test
     */
    public function checkSupportedTargetLanguages(): void
    {
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        static::assertContains('EN-GB', $deeplService->apiSupportedLanguages['target']);
        static::assertContains('EN-US', $deeplService->apiSupportedLanguages['target']);
        static::assertContains('DE', $deeplService->apiSupportedLanguages['target']);
        static::assertContains('UK', $deeplService->apiSupportedLanguages['target']);
        static::assertNotContains('BS', $deeplService->apiSupportedLanguages['target']);
    }

    /**
     * @test
     */
    public function checkSupportedSourceLanguages(): void
    {
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        static::assertContains('EN', $deeplService->apiSupportedLanguages['source']);
        static::assertContains('DE', $deeplService->apiSupportedLanguages['source']);
        static::assertContains('UK', $deeplService->apiSupportedLanguages['source']);
        static::assertNotContains('BS', $deeplService->apiSupportedLanguages['source']);
    }

    /**
     * @test
     */
    public function checkFormalitySupportedLanguages(): void
    {
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        static::assertContains('ES', $deeplService->formalitySupportedLanguages);
        static::assertContains('DE', $deeplService->formalitySupportedLanguages);
        static::assertContains('NL', $deeplService->formalitySupportedLanguages);
        static::assertNotContains('BS', $deeplService->formalitySupportedLanguages);
    }
}
