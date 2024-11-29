<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Access;

final class AllowedGlossarySyncAccess implements AccessItemInterface
{
    public const ALLOWED_GLOSSARY_SYNC = 'deepltranslate:allowedGlossarySync';

    public function getIdentifier(): string
    {
        return 'allowedGlossarySync';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:be_groups.deepltranslate_access.items.allowedGlossarySync.title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:be_groups.deepltranslate_access.items.allowedGlossarySync.description';
    }

    public function getIconIdentifier(): string
    {
        return 'deepl-logo';
    }
}