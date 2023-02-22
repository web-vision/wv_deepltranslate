<?php

/** @deprecated, only for v9 support  */

if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 10) {
    return [
        'deepl:glossary:cleanup' => [
            'class' => \WebVision\WvDeepltranslate\Command\GlossariesEntriesCleanupCommand::class,
        ],
        'deepl:glossary:list' => [
            'class' => \WebVision\WvDeepltranslate\Command\GlossariesEntriesListCommand::class,
        ],
    ];
}
