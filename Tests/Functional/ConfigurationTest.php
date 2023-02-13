<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Functional;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Configuration;

class ConfigurationTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/wv_deepltranslate',
    ];

    public function __construct()
    {
        parent::__construct();
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

        static::assertSame('api-free.deepl.com', $configuration->getApiUrl());
    }
}
