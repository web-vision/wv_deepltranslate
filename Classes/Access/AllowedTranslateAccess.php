<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Access;

final class AllowedTranslateAccess implements AccessItemInterface
{
    public const ALLOWED_TRANSLATE_OPTION_VALUE = 'deepltranslate:translateAllowed';

    public function getIdentifier(): string
    {
        return 'translateAllowed';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:be_groups.deepltranslate_access.items.translateAllowed.title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:be_groups.deepltranslate_access.items.translateAllowed.description';
    }

    public function getIconIdentifier(): string
    {
        return 'deepl-logo';
    }
}
