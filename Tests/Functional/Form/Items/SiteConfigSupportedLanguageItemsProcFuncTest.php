<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Form\Items;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;

/**
 * @covers \WebVision\WvDeepltranslate\Form\Item\SiteConfigSupportedLanguageItemsProcFunc
 */
class SiteConfigSupportedLanguageItemsProcFuncTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/wv_deepltranslate',
    ];

    protected function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();
    }

    /**
     * @test
     */
    public function getSupportedLanguageFormFields(): void
    {
        $func = GeneralUtility::makeInstance(SiteConfigSupportedLanguageItemsProcFunc::class);
        $fieldConfig = [];

        $func->getSupportedLanguageForField($fieldConfig);

        static::assertArrayHasKey('items', $fieldConfig);
        static::assertTrue(count($fieldConfig['items']) > 1);
    }
}
