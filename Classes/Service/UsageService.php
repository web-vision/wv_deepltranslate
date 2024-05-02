<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use DeepL\Usage;
use WebVision\WvDeepltranslate\ClientInterface;

final class UsageService implements UsageServiceInterface
{
    protected ClientInterface $client;

    public function __construct(
        ClientInterface $client
    ) {
        $this->client = $client;
    }

    public function getCurrentUsage(): ?Usage
    {
        return $this->client->getUsage();
    }

    public function checkTranslateLimitWillBeExceeded(string $contentToTranslate): bool
    {
        $usage = $this->getCurrentUsage();
        if ($usage === null) {
            return false;
        }
        if ($usage->character === null) {
            return true;
        }
        $currentCount = $usage->character->count;
        $toTranslateCount = strlen(strip_tags($contentToTranslate));
        return ($currentCount + $toTranslateCount) > $usage->character->limit;
    }

    /**
     * @inheritDoc
     */
    public function isTranslateLimitExceeded(): bool
    {
        $usage = $this->getCurrentUsage();
        if ($usage === null || $usage->character === null) {
            return false;
        }
        return $usage->character->count >= $usage->character->limit;
    }
}
