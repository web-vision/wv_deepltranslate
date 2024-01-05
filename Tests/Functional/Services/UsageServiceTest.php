<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Services;

use DeepL\TextResult;
use DeepL\Usage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\UsageService;

class UsageServiceTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected array $testExtensionsToLoad = [
        'web-vision/wv_deepltranslate',
    ];

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
     */
    public function classLoadable(): void
    {
        $usageService = $this->get(UsageService::class);

        static::assertInstanceOf(UsageService::class, $usageService);
    }

    /**
     * @test
     */
    public function usageReturnsValue(): void
    {
        /** @var UsageService $usageService */
        $usageService = $this->get(UsageService::class);

        $usage = $usageService->getCurrentUsage();

        static::assertInstanceOf(Usage::class, $usage);
    }

    /**
     * @test
     */
    public function limitExceedReturnsFalse(): void
    {
        /** @var UsageService $usageService */
        $usageService = $this->get(UsageService::class);

        static::assertFalse($usageService->isTranslateLimitExceeded(''));
    }

    /**
     * @test
     */
    public function limitExceedReturnsTrueIfLimitIsReached(): void
    {
        $translateContent = 'proton beam';

        /** @var UsageService $usageService */
        $usageService = $this->get(UsageService::class);

        $contentToTranslate = '';
        while (strlen($translateContent) < $usageService->getCurrentUsage()->character->limit) {
            $contentToTranslate .= sprintf(' %s', $translateContent);
        }

        /** @var DeeplService $deeplService */
        $deeplService = $this->get(DeeplService::class);

        $responseObject = $deeplService->translateRequest(
            $contentToTranslate,
            'DE',
            'EN'
        );

        $currentUsage = $usageService->getCurrentUsage();

        static::assertInstanceOf(TextResult::class, $responseObject);
    }
}
