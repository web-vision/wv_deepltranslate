<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional;

use DeepL\DeepLException;
use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\GlossaryLanguagePair;
use DeepL\Language;
use DeepL\TextResult;
use Helmich\JsonAssert\JsonAssertions;

/**
 * @covers \WebVision\WvDeepltranslate\Client
 */
final class ClientTest extends DeepLTestCase
{
    use JsonAssertions;

    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'web-vision/wv_deepltranslate',
    ];

    public function __construct(...$arguments)
    {
        parent::__construct(...$arguments);
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/Fixtures/ExtensionConfig.php'
        );
    }

    /**
     * @test
     */
    public function checkResponseFromTranslateContent(): void
    {
        $translateContent = self::EXAMPLE_TEXT['en'];
        $client = $this->makeClient();
        $response = $client->translate(
            $translateContent,
            'EN',
            'DE'
        );

        static::assertInstanceOf(TextResult::class, $response);
        static::assertSame(self::EXAMPLE_TEXT['de'], $response->text);
    }

    /**
     * @test
     */
    public function checkResponseFromSupportedTargetLanguage(): void
    {
        $client = $this->makeClient();
        $response = $client->getSupportedLanguageByType();

        static::assertIsArray($response);
        static::assertContainsOnlyInstancesOf(Language::class, $response);
    }

    /**
     * @test
     */
    public function checkResponseFromGlossaryLanguagePairs(): void
    {
        $client = $this->makeClient();
        $response = $client->getGlossaryLanguagePairs();

        static::assertIsArray($response);
        static::assertContainsOnlyInstancesOf(GlossaryLanguagePair::class, $response);
    }

    /**
     * @test
     */
    public function checkResponseFromCreateGlossary(): void
    {
        $client = $this->makeClient();
        $response = $client->createGlossary(
            'Deepl-Client-Create-Function-Test:' . __FUNCTION__,
            'de',
            'en',
            [
                0 => [
                    'source' => 'hallo Welt',
                    'target' => 'hello world',
                ],
            ],
        );

        static::assertInstanceOf(GlossaryInfo::class, $response);
        static::assertSame(1, $response->entryCount);
        static::assertIsString($response->glossaryId);
        static::assertInstanceOf(\DateTime::class, $response->creationTime);
    }

    /**
     * @test
     */
    public function checkResponseGetAllGlossaries(): void
    {
        $client = $this->makeClient();
        $response = $client->getAllGlossaries();

        static::assertIsArray($response);
        static::assertContainsOnlyInstancesOf(GlossaryInfo::class, $response);
    }

    /**
     * @test
     */
    public function checkResponseFromGetGlossary(): void
    {
        $client = $this->makeClient();
        $glossary = $client->createGlossary(
            'Deepl-Client-Create-Function-Test:' . __FUNCTION__,
            'de',
            'en',
            [
                0 => [
                    'source' => 'hallo Welt',
                    'target' => 'hello world',
                ],
            ],
        );

        $response = $client->getGlossary($glossary->glossaryId);

        static::assertInstanceOf(GlossaryInfo::class, $response);
        static::assertSame($glossary->glossaryId, $response->glossaryId);
        static::assertSame(1, $response->entryCount);
    }

    /**
     * @test
     */
    public function checkGlossaryDeletedNotCatchable(): void
    {
        $client = $this->makeClient();
        $glossary = $client->createGlossary(
            'Deepl-Client-Create-Function-Test' . __FUNCTION__,
            'de',
            'en',
            [
                0 => [
                    'source' => 'hallo Welt',
                    'target' => 'hello world',
                ],
            ],
        );

        $glossaryId = $glossary->glossaryId;

        $client->deleteGlossary($glossaryId);

        self::expectException(DeepLException::class);
        $deletedGlossary = $client->getGlossary($glossaryId);
    }

    /**
     * @test
     */
    public function checkResponseFromGetGlossaryEntries(): void
    {
        $client = $this->makeClient();
        $glossary = $client->createGlossary(
            'Deepl-Client-Create-Function-Test:' . __FUNCTION__,
            'de',
            'en',
            [
                0 => [
                    'source' => 'hallo Welt',
                    'target' => 'hello world',
                ],
            ],
        );

        $response = $client->getGlossaryEntries($glossary->glossaryId);

        static::assertInstanceOf(GlossaryEntries::class, $response);
        static::assertSame(1, count($response->getEntries()));
    }
}
