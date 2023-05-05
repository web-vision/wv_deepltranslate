<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Configuration;

class ConfigurationTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/wv_deepltranslate',
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
            static::assertSame('ddev-deepltranslate-deeplmockserver:3000', $configuration->getApiUrl());
        }
    }
}
