<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use DeepL\Language;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Tests\Functional\AbstractDeepLTestCase;

/**
 * @covers \WebVision\WvDeepltranslate\Service\DeeplService
 */
final class DeeplServiceTest extends AbstractDeepLTestCase
{
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
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            self::EXAMPLE_TEXT['de'],
            'EN-GB',
            'DE'
        );

        static::assertSame(self::EXAMPLE_TEXT['en'], $responseObject->text);
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

        static::assertContainsOnlyInstancesOf(Language::class, $deeplService->getSupportLanguage()['target']);

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
