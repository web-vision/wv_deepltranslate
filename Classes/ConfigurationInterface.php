<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

interface ConfigurationInterface
{
    public function getApiKey(): string;

    /**
     * @deprecated In a future version, "Formality" should be moved to the SiteConfig
     */
    public function getFormality(): string;
}
