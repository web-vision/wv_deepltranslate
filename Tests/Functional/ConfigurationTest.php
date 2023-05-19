<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Configuration;

class ConfigurationTest extends FunctionalTestCase
{
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
    public function checkApiParseUrlAndGiveOnlyDomain(): void
    {
        $configuration = GeneralUtility::makeInstance(Configuration::class);

        if (defined('DEEPL_API_KEY') && getenv('DEEPL_API_KEY') !== '') {
            static::assertSame('api-free.deepl.com', $configuration->getApiUrl());
        } else {
            $parsedUrl = parse_url(
                $this->configurationToUseInTestInstance['EXTENSIONS']['wv_deepltranslate']['apiUrl']
                    ?? 'http://ddev-deepltranslate-deeplmockserver:3000'
            );
            $checkApiUrl = $parsedUrl['host'] . ($parsedUrl['port'] ? ':' . $parsedUrl['port'] : '');
            static::assertSame(
                $checkApiUrl,
                $configuration->getApiUrl()
            );
        }
    }
}
