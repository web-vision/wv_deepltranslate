<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Event;

/**
 * Represents an event to disallow a specific database table from DeepL translation.
 * Allows control over whether translation buttons are permitted for the specified table.
 */
final class DisallowTableFromDeeplTranslateEvent
{
    public function __construct(
        public readonly string $tableName,
        private bool $translateButtonsAllowed = true
    ) {
    }

    public function isTranslateButtonsAllowed(): bool
    {
        return $this->translateButtonsAllowed;
    }

    public function disallowTranslateButtons(): void
    {
        $this->translateButtonsAllowed = false;
    }
}
