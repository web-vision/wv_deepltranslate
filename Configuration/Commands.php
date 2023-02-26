<?php

/** @deprecated, only for v9 support  */
if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 10) {
    return [
        'deepl:glossary:cleanup' => [
            'class' => \WebVision\WvDeepltranslate\Command\GlossaryCleanupCommand::class,
        ],
        'deepl:glossary:list' => [
            'class' => \WebVision\WvDeepltranslate\Command\GlossaryListCommand::class,
        ],
        'deepl:glossary:sync' => [
            'class' => \WebVision\WvDeepltranslate\Command\GlossarySyncCommand::class,
        ],
    ];
}
