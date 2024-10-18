<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

// @todo Consider to rename/move this as service class.
final class GlossaryEntryRepository
{
    public const TABLE_NAME = 'tx_wvdeepltranslate_glossaryentry';

    protected Connection $connection;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connection = $connectionPool->getConnectionForTable(self::TABLE_NAME);
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
            self::TABLE_NAME,
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
            self::TABLE_NAME,
            [
                'uid' => $uid,
            ]
        );

        // @todo Should we not better returning null instead of an empty array if nor recourd could be retrieved ?
        return $result->fetchAssociative() ?: [];
    }

    /**
     * @param array<mixed> $entry
     * @return int
     */
    public function add(array $entry): int
    {
        $this->connection->insert(self::TABLE_NAME, $entry);

        return (int) $this->connection->lastInsertId(self::TABLE_NAME);
    }
}
