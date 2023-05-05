<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GlossaryEntryRepository
{
    /**
     * @deprecated
     */
    public function hasEntriesForGlossary(int $parentId): bool
    {
        $entries = $this->findEntriesByGlossary($parentId);
        return count($entries) > 0;
    }

    /**
     * @return array<string, mixed>
     * @deprecated
     */
    public function findEntriesByGlossary(int $parentId): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossaryentry');

        $result = $connection->select(
            ['*'],
            'tx_wvdeepltranslate_glossaryentry',
            [
                'glossary' => $parentId,
            ]
        );

        return $result->fetchAll() ?: [];
    }

    /**
     * @return array{uid: int}
     */
    public function findEntryByUid(int $uid): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossaryentry');

        $result = $connection->select(
            ['*'],
            'tx_wvdeepltranslate_glossaryentry',
            [
                'uid' => $uid,
            ]
        );

        return $result->fetch() ?: [];
    }
}
