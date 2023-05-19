<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Service\DeeplService;

/**
 * @covers \WebVision\WvDeepltranslate\Service\DeeplService
 */
class DeeplServiceTest extends FunctionalTestCase
{
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
    }

    /**
     * @test
     */
    public function translateContentFromDeToEn(): void
    {
        if (defined('DEEPL_MOCKSERVER_USED') && DEEPL_MOCKSERVER_USED === true) {
            static::markTestSkipped(__METHOD__ . ' skipped, because DEEPL MOCKSERVER do not support EN as TARGET language.');
        }
        $serverParams = array_replace($_SERVER, ['HTTP_HOST' => 'example.com', 'SCRIPT_NAME' => '/typo3/index.php']);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('http://example.com/typo3/index.php', 'GET', null, $serverParams))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($serverParams));

        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            'Ich möchte gern übersetzt werden!',
            'EN',
            'DE'
        );

        static::assertSame('I would like to be translated!', $responseObject['translations'][0]['text']);
    }

    /**
     * @test
     */
    public function translateContentFromEnToDe(): void
    {
        $translateContent = 'I would like to be translated!';
        $expectedTranslation = 'Ich möchte gern übersetzt werden!';
        if (defined('DEEPL_MOCKSERVER_USED') && DEEPL_MOCKSERVER_USED === true) {
            $translateContent = 'proton beam';
            $expectedTranslation = 'Protonenstrahl';
        }
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'EN'
        );

        static::assertSame($expectedTranslation, $responseObject['translations'][0]['text']);
    }

    /**
     * @test
     */
    public function translateContentWithAutoDetectSourceParam(): void
    {
        $translateContent = 'I would like to be translated!';
        $expectedTranslation = 'Ich möchte gern übersetzt werden!';
        if (defined('DEEPL_MOCKSERVER_USED') && DEEPL_MOCKSERVER_USED === true) {
            $translateContent = 'proton beam';
            $expectedTranslation = 'Protonenstrahl';
        }
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'auto'
        );

        static::assertSame($expectedTranslation, $responseObject['translations'][0]['text']);
    }

    /**
     * @test
     */
    public function checkSupportedTargetLanguages(): void
    {
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        static::assertContains('EN-GB', (array)$deeplService->apiSupportedLanguages['target']);
        static::assertContains('EN-US', (array)$deeplService->apiSupportedLanguages['target']);
        static::assertContains('DE', (array)$deeplService->apiSupportedLanguages['target']);
        static::assertContains('UK', (array)$deeplService->apiSupportedLanguages['target']);
        static::assertNotContains('EN', (array)$deeplService->apiSupportedLanguages['target']);
        static::assertNotContains('BS', (array)$deeplService->apiSupportedLanguages['target']);
    }

    /**
     * @test
     */
    public function checkFormalitySupportedLanguages(): void
    {
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        static::assertContains('ES', (array)$deeplService->formalitySupportedLanguages);
        static::assertContains('DE', (array)$deeplService->formalitySupportedLanguages);
        static::assertContains('NL', (array)$deeplService->formalitySupportedLanguages);
        static::assertNotContains('EN', (array)$deeplService->formalitySupportedLanguages);
        static::assertNotContains('BS', (array)$deeplService->formalitySupportedLanguages);
    }

    /**
     * @test
     */
    public function checkSupportedSourceLanguages(): void
    {
        $deeplService = GeneralUtility::makeInstance(DeeplService::class);

        static::assertContains('DE', (array)$deeplService->apiSupportedLanguages['source']);
        static::assertContains('UK', (array)$deeplService->apiSupportedLanguages['source']);
        static::assertContains('EN', (array)$deeplService->apiSupportedLanguages['source']);
        static::assertNotContains('EN-GB', (array)$deeplService->apiSupportedLanguages['source']);
        static::assertNotContains('EN-US', (array)$deeplService->apiSupportedLanguages['source']);
        static::assertNotContains('BS', (array)$deeplService->apiSupportedLanguages['source']);
    }
}
