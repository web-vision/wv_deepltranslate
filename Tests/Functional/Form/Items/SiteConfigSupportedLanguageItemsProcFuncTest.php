<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Form\Items;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;
use WebVision\WvDeepltranslate\Tests\Functional\AbstractDeepLTestCase;

/**
 * @covers \WebVision\WvDeepltranslate\Form\Item\SiteConfigSupportedLanguageItemsProcFunc
 */
final class SiteConfigSupportedLanguageItemsProcFuncTest extends AbstractDeepLTestCase
{
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
        static::assertTrue((count($fieldConfig['items']) > 2));
    }
}
