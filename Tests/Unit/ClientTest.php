<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use WebVision\WvDeepltranslate\Client;
use WebVision\WvDeepltranslate\ConfigurationInterface;
use WebVision\WvDeepltranslate\Exception\ApiKeyNotSetException;

class ClientTest extends UnitTestCase
{

    private function createMockConfigurationWithEmptyApiKey(): MockObject
    {
        $mockConfiguration = $this->getMockBuilder(ConfigurationInterface::class)
            ->getMock();

        $mockConfiguration
            ->method('getApiKey')
            ->willReturn('');

        return $mockConfiguration;
    }

    /**
     * @test
     */
    public function throwErrorGetSupportedLanguageByTypeWhenApiKeyNotSet(): void
    {
        /** @var ConfigurationInterface $configurationMock */
        $configurationMock = $this->createMockConfigurationWithEmptyApiKey();
        $client = new Client($configurationMock);

        static::expectException(ApiKeyNotSetException::class);
        static::expectExceptionCode(1708081233823);
        static::expectExceptionMessage('The api key ist not set');

        $client->getSupportedLanguageByType();
    }

    /**
     * @test
     */
    public function throwErrorGetGlossaryLanguagePairsWhenApiKeyNotSet(): void
    {
        /** @var ConfigurationInterface $configurationMock */
        $configurationMock = $this->createMockConfigurationWithEmptyApiKey();
        $client = new Client($configurationMock);

        static::expectException(ApiKeyNotSetException::class);
        static::expectExceptionCode(1708081233823);
        static::expectExceptionMessage('The api key ist not set');

        $client->getGlossaryLanguagePairs();
    }

    /**
     * @test
     */
    public function throwErrorCreateGlossaryEntriesWhenApiKeyNotSet(): void
    {
        /** @var ConfigurationInterface $configurationMock */
        $configurationMock = $this->createMockConfigurationWithEmptyApiKey();
        $client = new Client($configurationMock);

        static::expectException(ApiKeyNotSetException::class);
        static::expectExceptionCode(1708081233823);
        static::expectExceptionMessage('The api key ist not set');

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
    }

    /**
     * @test
     */
    public function throwErrorGetGlossaryWhenApiKeyNotSet(): void
    {
        /** @var ConfigurationInterface $configurationMock */
        $configurationMock = $this->createMockConfigurationWithEmptyApiKey();
        $client = new Client($configurationMock);

        static::expectException(ApiKeyNotSetException::class);
        static::expectExceptionCode(1708081233823);
        static::expectExceptionMessage('The api key ist not set');

        $response = $client->getGlossary('61567955-8db8-493d-aa20-28bbba6fb438');
    }

    /**
     * @test
     */
    public function throwErrorDeletedGlossaryWhenApiKeyNotSet(): void
    {
        /** @var ConfigurationInterface $configurationMock */
        $configurationMock = $this->createMockConfigurationWithEmptyApiKey();
        $client = new Client($configurationMock);

        static::expectException(ApiKeyNotSetException::class);
        static::expectExceptionCode(1708081233823);
        static::expectExceptionMessage('The api key ist not set');

        $client->deleteGlossary('25d90db6-bcab-4130-ab36-4514dd5d87ec');
    }

    /**
     * @test
     */
    public function throwErrorGlossaryEntriesWhenApiKeyNotSet(): void
    {
        /** @var ConfigurationInterface $configurationMock */
        $configurationMock = $this->createMockConfigurationWithEmptyApiKey();
        $client = new Client($configurationMock);

        static::expectException(ApiKeyNotSetException::class);
        static::expectExceptionCode(1708081233823);
        static::expectExceptionMessage('The api key ist not set');

        $response = $client->getGlossaryEntries('a44703d5-ece7-4230-a67b-1a07153768d6');
    }

    /**
     * @test
     */
    public function throwErrorTranslationExceptionWhenApiKeyNotSet(): void
    {
        /** @var ConfigurationInterface $configurationMock */
        $configurationMock = $this->createMockConfigurationWithEmptyApiKey();
        $client = new Client($configurationMock);

        static::expectException(ApiKeyNotSetException::class);
        static::expectExceptionCode(1708081233823);
        static::expectExceptionMessage('The api key ist not set');

        $client->translate(
            'proton beam',
            'DE',
            'EN'
        );
    }
}
