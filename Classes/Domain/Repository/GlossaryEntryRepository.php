<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GlossaryEntryRepository
{
    /**
     * @return array{uid: int}
     * @throws Exception
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

        return $result->fetchAssociative() ?: [];
    }
}
