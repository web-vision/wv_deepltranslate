<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use DeepL\Language;
use WebVision\WvDeepltranslate\Domain\Dto\TranslateContext;
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
     * @deprecated if the @see DeeplService::translateRequest() function has been removed
     */
    public function translateContentFromDeToEn(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $translateContent = $deeplService->translateRequest(
            self::EXAMPLE_TEXT['de'],
            'EN-GB',
            'DE'
        );

        static::assertSame(self::EXAMPLE_TEXT['en'], $translateContent);
    }

    /**
     * @test
     * @deprecated if the @see DeeplService::translateRequest() function has been removed
     */
    public function translateContentFromEnToDe(): void
    {
        $translateContent = 'proton beam';
        $expectedTranslation = 'Protonenstrahl';
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $translateContent = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'EN'
        );

        static::assertSame($expectedTranslation, $translateContent);
    }

    /**
     * @test
     * @deprecated entfällt wenn die Funktion @see DeeplService::translateRequest() entfernt wurde
     */
    public function translateContentWithAutoDetectSourceParam(): void
    {
        $translateContent = 'proton beam';
        $expectedTranslation = 'Protonenstrahl';
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $translateContent = $deeplService->translateRequest(
            $translateContent,
            'DE',
            'auto'
        );

        static::assertSame($expectedTranslation, $translateContent);
    }

    /**
     * @test
     */
    public function translateContentWithTranslateContextFromDeToEn(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $translateContext = new TranslateContext('Protonenstrahl');
        $translateContext->setSourceLanguageCode('DE');
        $translateContext->setTargetLanguageCode('EN-GB');

        $translateContent = $deeplService->translateContent($translateContext);

        static::assertSame('proton beam', $translateContent);
    }

    /**
     * @test
     */
    public function translateContentWithTranslateContextFromEnToDe(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $translateContext = new TranslateContext('proton beam');
        $translateContext->setSourceLanguageCode('EN');
        $translateContext->setTargetLanguageCode('DE');

        $translateContent = $deeplService->translateContent($translateContext);

        static::assertSame('Protonenstrahl', $translateContent);
    }

    /**
     * @test
     */
    public function translateContentWithTranslateContextWithAutoDetectSourceParam(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $translateContext = new TranslateContext('proton beam');
        $translateContext->setSourceLanguageCode('auto');
        $translateContext->setTargetLanguageCode('DE');

        $translateContent = $deeplService->translateContent($translateContext);

        static::assertSame('Protonenstrahl', $translateContent);
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
    public function checkIsTargetLanguageSupported(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        static::assertTrue($deeplService->isTargetLanguageSupported('DE'));
        // We should avoid using a real existing language here, as the tests will fail,
        // if the language gets supported by DeepL and the mock server is updated.
        static::assertFalse($deeplService->isTargetLanguageSupported('BS'));
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

    /**
     * @test
     */
    public function checkIsSourceLanguageSupported(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        static::assertTrue($deeplService->isSourceLanguageSupported('DE'));
    }

    /**
     * @test
     */
    public function checkHasLanguageFormalitySupport(): void
    {
        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $hasFormalitySupport = $deeplService->hasLanguageFormalitySupport('DE');
        static::assertTrue($hasFormalitySupport);
        $hasNotFormalitySupport = $deeplService->hasLanguageFormalitySupport('EN-GB');
        static::assertFalse($hasNotFormalitySupport);
    }

}
