<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use DeepL\Usage;
use WebVision\WvDeepltranslate\ClientInterface;

final class UsageService implements UsageServiceInterface
{
    protected ClientInterface $client;

    protected Usage $usage;

    public function __construct(
        ClientInterface $client
    ) {
        $this->client = $client;
        $this->updateUsage();
    }

    public function getCurrentUsage(): Usage
    {
        $this->updateUsage();
        return $this->usage;
    }

    public function isTranslateLimitExceeded(string $contentToTranslate): bool
    {
        $this->updateUsage();
        if ($this->usage->character === null) {
            return true;
        }
        $currentCount = $this->usage->character->count;
        // @todo: clarify, if html tags count or not
        $toTranslateCount = strlen(strip_tags($contentToTranslate));

        return ($currentCount + $toTranslateCount) > $this->usage->character->limit;
    }

    private function updateUsage(): void
    {
        $this->usage = $this->client->getUsage();
    }
}
