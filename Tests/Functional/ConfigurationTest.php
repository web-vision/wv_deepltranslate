<?php

declare(strict_types = 1);

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

    public function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/Fixtures/ExtensionConfig.php'
        );

        parent::setUp();
    }


    /**
     * @test
     */
    public function checkApiParseUrlAndGiveOnlyDomain(): void
    {
        $configuration = GeneralUtility::makeInstance(Configuration::class);

        static::assertSame('api-free.deepl.com', $configuration->getApiUrl());
    }
}
