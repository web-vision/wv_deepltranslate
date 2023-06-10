<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Tests\Functional\DeepLTestCase;

/**
 * @covers \WebVision\WvDeepltranslate\Service\DeeplService
 */
final class DeeplServiceTest extends DeepLTestCase
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
        $this->needsRealServer();

        $serverParams = array_replace($_SERVER, ['HTTP_HOST' => 'example.com', 'SCRIPT_NAME' => '/typo3/index.php']);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('http://example.com/typo3/index.php', 'GET', null, $serverParams))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($serverParams));

        $deeplService = $this->get(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            'Ich möchte gerne übersetzt werden!',
            'EN',
            'DE'
        );

        self::assertSame('I would like to be translated!', $responseObject['translations'][0]['text']);
    }

    /**
     * @test
     */
    public function translateContentFromEnToDe(): void
    {
        $translateContent = 'I would like to be translated!';
        $expectedTranslation = 'Ich möchte gerne übersetzt werden!';
        if (defined('DEEPL_MOCKSERVER_USED') && DEEPL_MOCKSERVER_USED === true) {
            $translateContent = 'proton beam';
            $expectedTranslation = 'Protonenstrahl';
        }
        $deeplService = $this->get(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'EN'
        );

        self::assertSame($expectedTranslation, $responseObject->text);
    }

    /**
     * @test
     */
    public function translateContentWithAutoDetectSourceParam(): void
    {
        $translateContent = 'I would like to be translated!';
        $expectedTranslation = 'Ich möchte gerne übersetzt werden!';
        if (defined('DEEPL_MOCKSERVER_USED') && DEEPL_MOCKSERVER_USED === true) {
            $translateContent = 'proton beam';
            $expectedTranslation = 'Protonenstrahl';
        }
        $deeplService = $this->get(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'auto'
        );

        self::assertSame($expectedTranslation, $responseObject['translations'][0]['text']);
    }

    /**
     * @test
     */
    public function checkSupportedTargetLanguages(): void
    {
        $deeplService = $this->get(DeeplService::class);

        self::assertContains('EN-GB', (array)$deeplService->apiSupportedLanguages['target']);
        self::assertContains('EN-US', (array)$deeplService->apiSupportedLanguages['target']);
        self::assertContains('DE', (array)$deeplService->apiSupportedLanguages['target']);
        self::assertContains('UK', (array)$deeplService->apiSupportedLanguages['target']);
        self::assertNotContains('EN', (array)$deeplService->apiSupportedLanguages['target']);
        self::assertNotContains('BS', (array)$deeplService->apiSupportedLanguages['target']);
    }

    /**
     * @test
     */
    public function checkFormalitySupportedLanguages(): void
    {
        $deeplService = $this->get(DeeplService::class);

        self::assertContains('ES', (array)$deeplService->formalitySupportedLanguages);
        self::assertContains('DE', (array)$deeplService->formalitySupportedLanguages);
        self::assertContains('NL', (array)$deeplService->formalitySupportedLanguages);
        self::assertNotContains('EN', (array)$deeplService->formalitySupportedLanguages);
        self::assertNotContains('BS', (array)$deeplService->formalitySupportedLanguages);
    }

    /**
     * @test
     */
    public function checkSupportedSourceLanguages(): void
    {
        $deeplService = $this->get(DeeplService::class);

        self::assertContains('DE', (array)$deeplService->apiSupportedLanguages['source']);
        self::assertContains('UK', (array)$deeplService->apiSupportedLanguages['source']);
        self::assertContains('EN', (array)$deeplService->apiSupportedLanguages['source']);
        self::assertNotContains('EN-GB', (array)$deeplService->apiSupportedLanguages['source']);
        self::assertNotContains('EN-US', (array)$deeplService->apiSupportedLanguages['source']);
        self::assertNotContains('BS', (array)$deeplService->apiSupportedLanguages['source']);
    }
}
