<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use DateTimeImmutable;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GlossaryRepository
{
    /**
     * @param int $uid
     * @return array{name: string, id: string, source_lang: string, target_lang: string, entries: array}
     */
    public function getGlossaryInformationForSync(int $uid): array
    {
        $glossaryInformation = [
            'entries' => [],
        ];
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_glossary');
        $statement = $db
            ->select(
                'glossary_name',
                'glossary_id',
                'source_lang',
                'target_lang'
            )
            ->from('tx_wvdeepltranslate_glossary')
            ->where(
                $db->expr()->eq('uid', $db->createNamedParameter($uid, Connection::PARAM_INT))
            );

        $glossary = $statement->execute()->fetch();
        $glossaryInformation['name'] = $glossary['glossary_name'];
        $glossaryInformation['id'] = $glossary['glossary_id'];
        $glossaryInformation['source_lang'] = $glossary['source_lang'];
        $glossaryInformation['target_lang'] = $glossary['target_lang'];

        $statement->getConnection()->close();

        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_glossaryentry');
        $statement = $db
            ->select('source', 'target')
            ->from('tx_wvdeepltranslate_glossaryentry')
            ->where(
                $db->expr()->eq('glossary', $db->createNamedParameter($uid, Connection::PARAM_INT))
            );

        $entries = $statement->execute()->fetchAll();
        if ($entries !== false) {
            foreach ($entries as $entry) {
                $glossaryInformation['entries'][] = $entry;
            }
        }

        return $glossaryInformation;
    }

    public function detectGlossaryForTranslation(
        string $table,
        int $elementId,
        string $sourceLanguageId,
        string $targetLanguageId
    ): string {
        return '';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByGlossaryId(string $glossaryId): ?array
    {
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $result = $db->select(
            ['*'],
            'tx_wvdeepltranslate_glossary',
            [
                'glossary_id' => $glossaryId,
            ]
        );

        if ($result->rowCount() === 0) {
            return null;
        }

        return $result->fetch();
    }

    /**
     * @param array{
     *     glossary_id?: string,
     *     name?: string,
     *     ready?: bool,
     *     source_lang?: string,
     *     target_lang?: string,
     *     creation_time?: string,
     *     entry_count?: int
     * } $information
     */
    public function updateLocalGlossary(array $information, int $uid): void
    {
        $glossarySyncTimestamp = 0;
        if (isset($information['creation_time'])) {
            $glossarySyncTimestamp = DateTimeImmutable::createFromFormat(
                'Y-m-d\TH:i:s.uT',
                $information['creation_time']
            )->getTimestamp();
        }

        $insertParams = [
            'glossary_id' => $information['glossary_id'] ?? '',
            'glossary_ready' => $information['ready'] ? 1 : 0,
            'glossary_lastsync' => $glossarySyncTimestamp,
        ];

        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $db->update(
            'tx_wvdeepltranslate_glossary',
            $insertParams,
            [
                'uid' => $uid,
            ]
        );
    }

    public function hasGlossariesOnPage(int $pageId): bool
    {
        $glossaries = $this->findAllGlossaries($pageId);
        return count($glossaries) > 0;
    }

    public function findAllGlossaries(int $pageId = 0): array
    {
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $identifiers = [];
        if ($pageId > 0) {
            $identifiers = [
                'pid' => $pageId,
            ];
        }
        return $db->select(
            ['uid'],
            'tx_wvdeepltranslate_glossary',
            $identifiers
        )->fetchAll() ?: [];
    }

    public function getGlossaryBySourceAndTarget(string $sourceLanguage, string $targetLanguage): ?string
    {
        $sourceLang = strtolower($sourceLanguage);
        $targetLang = strtolower($targetLanguage);
        if (strlen($targetLang) > 2) {
            $targetLang = substr($targetLang, 0, 2);
        }

        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary');
        $statement = $db
            ->select(
                ['glossary_id'],
                'tx_wvdeepltranslate_glossary',
                [
                    'source_lang' => $sourceLang,
                    'target_lang' => $targetLang,
                ]
            );
        $result = $statement->fetch();
        if ($result === false) {
            return '';
        }

        return $result['glossary_id'];
    }

    public function removeGlossarySync(string $glossaryId): bool
    {
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $count = $db->update(
            'tx_wvdeepltranslate_glossary',
            [
                'glossary_id' => '',
                'glossary_lastsync' => 0,
                'glossary_ready' => 0,
            ],
            [
                'glossary_id' => $glossaryId,
            ]
        );

        return $count >= 1;
    }

    public function getGlossariesDeeplIdSet(): array
    {
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_glossary');
        $statement = $db
            ->select('uid', 'glossary_id')
            ->from('tx_wvdeepltranslate_glossary')
            ->where(
                $db->expr()->neq('glossary_id', $db->createNamedParameter(''))
            );

        $result = $statement->execute()->fetch();
        if ($result === false) {
            return [];
        }

        return $result;
    }
}
