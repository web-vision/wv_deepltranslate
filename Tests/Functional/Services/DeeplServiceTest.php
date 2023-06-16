<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use DeepL\Language;
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

        static::assertSame('I would like to be translated!', $responseObject->text);
    }

    /**
     * @test
     */
    public function translateContentFromEnToDe(): void
    {
        $translateContent = 'proton beam';
        $expectedTranslation = 'Protonenstrahl';
        $deeplService = $this->get(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'EN'
        );

        static::assertSame($expectedTranslation, $responseObject->text);
    }

    /**
     * @test
     */
    public function translateContentWithAutoDetectSourceParam(): void
    {
        $translateContent = 'proton beam';
        $expectedTranslation = 'Protonenstrahl';
        $deeplService = $this->get(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'auto'
        );

        static::assertSame($expectedTranslation, $responseObject->text);
    }

    /**
     * @test
     */
    public function checkSupportedTargetLanguages(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        static::assertContainsOnlyInstancesOf(Language::class, $deeplService->apiSupportedLanguages['target']);

        static::assertEquals('EN-GB', $deeplService->detectTargetLanguage('EN-GB')->code);
        static::assertEquals('EN-US', $deeplService->detectTargetLanguage('EN-US')->code);
        static::assertEquals('DE', $deeplService->detectTargetLanguage('DE')->code);
        static::assertEquals('UK', $deeplService->detectTargetLanguage('UK')->code);
        static::assertNull($deeplService->detectTargetLanguage('EN'));
        static::assertNull($deeplService->detectTargetLanguage('BS'));
    }

    /**
     * @test
     */
    public function checkSupportedSourceLanguages(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        static::assertEquals('DE', $deeplService->detectSourceLanguage('DE')->code);
        static::assertEquals('UK', $deeplService->detectSourceLanguage('UK')->code);
        static::assertEquals('EN', $deeplService->detectSourceLanguage('EN')->code);
        static::assertNull($deeplService->detectSourceLanguage('EN-GB'));
        static::assertNull($deeplService->detectSourceLanguage('EN-US'));
        static::assertNull($deeplService->detectSourceLanguage('BS'));
    }
}
