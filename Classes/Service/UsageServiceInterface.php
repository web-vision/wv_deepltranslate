<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Service;

use DeepL\Usage;

interface UsageServiceInterface
{
    public function getCurrentUsage(): ?Usage;
    public function checkTranslateLimitWillBeExceeded(string $contentToTranslate): bool;

    /**
     * In Deepl Usages Object all limits are checked if they are reached.
     * However, we only want to check whether the character limit has been reached.
     *
     * @see Usage::anyLimitReached()
     */
    public function isTranslateLimitExceeded(): bool;
}
