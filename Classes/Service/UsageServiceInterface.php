<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use DeepL\Usage;

interface UsageServiceInterface
{
    public function getCurrentUsage(): ?Usage;

    public function isTranslateLimitExceeded(string $contentToTranslate): bool;
}
