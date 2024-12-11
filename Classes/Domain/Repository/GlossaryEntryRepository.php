<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// @todo Consider to rename/move this as service class.
final class GlossaryEntryRepository
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

        return $result->fetchAllAssociative() ?: [];
    }

    /**
     * @return array<non-empty-string, mixed>
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

        // @todo Should we not better returning null instead of an empty array if nor recourd could be retrieved ?
        return $result->fetchAssociative() ?: [];
    }
}
