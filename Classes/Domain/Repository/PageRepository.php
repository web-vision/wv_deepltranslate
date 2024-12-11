<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Domain\Repository;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// @todo Consider to rename/move this as service class.
final class PageRepository
{
    public function markPageAsTranslatedWithDeepl(int $pageId, int $targetLanguageId): void
    {
        /** @var DateTimeAspect $dateTimeAspect */
        $dateTimeAspect = GeneralUtility::makeInstance(Context::class)->getAspect('date');

        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->update(
                'pages',
                [
                    'tx_wvdeepltranslate_content_not_checked' => 1,
                    'tx_wvdeepltranslate_translated_time' => $dateTimeAspect->getDateTime()->getTimestamp(),
                ],
                [
                    'l10n_parent' => $pageId,
                    'sys_language_uid' => $targetLanguageId,
                ],
                [
                    Connection::PARAM_INT, // @todo With 12.4+ minimal support this can be omitted.
                    Connection::PARAM_INT, // @todo With 12.4+ minimal support this can be omitted.
                ]
            );
    }
}
