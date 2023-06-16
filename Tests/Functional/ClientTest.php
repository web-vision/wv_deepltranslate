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

        self::assertInstanceOf(TextResult::class, $response);
        self::assertSame(self::EXAMPLE_TEXT['de'], $response->text);
    }

    /**
     * @test
     */
    public function checkResponseFromSupportedTargetLanguage(): void
    {
        $client = $this->makeClient();
        $response = $client->getSupportedLanguageByType();

        self::assertIsArray($response);
        self::assertContainsOnlyInstancesOf(Language::class, $response);
    }

    /**
     * @test
     */
    public function checkResponseFromGlossaryLanguagePairs(): void
    {
        $client = $this->makeClient();
        $response = $client->getGlossaryLanguagePairs();

        self::assertIsArray($response);
        self::assertContainsOnlyInstancesOf(GlossaryLanguagePair::class, $response);
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

        self::assertInstanceOf(GlossaryInfo::class, $response);
        self::assertSame(1, $response->entryCount);
        self::assertIsString($response->glossaryId);
        self::assertInstanceOf(\DateTime::class, $response->creationTime);
    }

    /**
     * @test
     */
    public function checkResponseGetAllGlossaries(): void
    {
        $client = $this->makeClient();
        $response = $client->getAllGlossaries();

        self::assertIsArray($response);
        self::assertContainsOnlyInstancesOf(GlossaryInfo::class, $response);
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

        self::assertInstanceOf(GlossaryInfo::class, $response);
        self::assertSame($glossary->glossaryId, $response->glossaryId);
        self::assertSame(1, $response->entryCount);
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

        self::assertNull($client->getGlossary($glossaryId));
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

        self::assertInstanceOf(GlossaryEntries::class, $response);
        self::assertSame(1, count($response->getEntries()));
    }
}
