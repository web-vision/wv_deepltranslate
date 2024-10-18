<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

// @todo Consider to rename/move this as service class.
final class GlossaryEntryRepository
{
    protected Connection $connection;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connection = $connectionPool->getConnectionForTable('tx_wvdeepltranslate_glossaryentry');
    }

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
        $result = $this->connection->select(
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
        $result = $this->connection->select(
            ['*'],
            'tx_wvdeepltranslate_glossaryentry',
            [
                'uid' => $uid,
            ]
        );

        // @todo Should we not better returning null instead of an empty array if nor recourd could be retrieved ?
        return $result->fetchAssociative() ?: [];
    }

    public function findBy(array $identifiers): ?array
    {
        $result = $this->connection->select(
            ['*'],
            'tx_wvdeepltranslate_glossaryentry',
            $identifiers
        )->fetchAssociative();

        return is_array($result) ? $result : null;
    }
}
