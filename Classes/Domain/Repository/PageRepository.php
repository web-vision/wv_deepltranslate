<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// @todo Consider to rename/move this as service class.
final class PageRepository
{
    /**
     * @param array{uid: int} $targetLanguage
     * @todo Is the array shape for $targetLangauge correct/complete ?
     */
    public function markPageAsTranslatedWithDeepl(int $pageId, array $targetLanguage): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->update(
                'pages',
                [
                    'tx_wvdeepltranslate_content_not_checked' => 1,
                    // @todo Consider to use $GLOBALS['EXEC_TIME'] or $GLOBALS['SIM_EXEC_TIME'] instead of time().
                    'tx_wvdeepltranslate_translated_time' => time(),
                ],
                [
                    'l10n_parent' => $pageId,
                    'sys_language_uid' => $targetLanguage['uid'],
                ],
                [
                    Connection::PARAM_INT, // @todo With 12.4+ minimal support this can be omitted.
                    Connection::PARAM_INT, // @todo With 12.4+ minimal support this can be omitted.
                ]
            );
    }
}
