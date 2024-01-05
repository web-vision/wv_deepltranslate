<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use DeepL\Usage;
use WebVision\WvDeepltranslate\Client;

final class UsageService implements UsageServiceInterface
{
    protected Client $client;

    protected ?Usage $usage;

    public function __construct(
        Client $client
    ) {
        $this->client = $client;
        $this->updateUsage();
    }

    public function getCurrentUsage(): ?Usage
    {
        $this->updateUsage();
        return $this->usage;
    }

    public function isTranslateLimitExceeded(string $contentToTranslate): bool
    {
        $this->updateUsage();
        $currentCount = $this->usage->character->count;
        $toTranslateCount = strlen(strip_tags($contentToTranslate));

        return ($currentCount + $toTranslateCount) > $this->usage->character->limit;
    }

    private function updateUsage(): void
    {
        $this->usage = $this->client->getUsage();
    }
}
