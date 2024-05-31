<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use DeepL\GlossaryInfo;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Factory\GlossaryFactory;

// @todo Consider to rename/move this as service class.
final class GlossaryRepository
{
    private ConnectionPool $connectionPool;

    public function __construct(
        ConnectionPool $connectionPool
    ) {
        $this->connectionPool = $connectionPool;
    }

    /**
     * @deprecated and will be removed with later version, please use see WebVision\WvDeepltranslate\Factory\GlossaryFactory::createGlossaryInformation()
     */
    public function getGlossaryInformationForSync(int $pageId): array
    {
        /** @var GlossaryFactory $glossaryFactory */
        $glossaryFactory = GeneralUtility::makeInstance(GlossaryFactory::class);
        return $glossaryFactory->createGlossaryInformation($pageId);
    }

    /**
     * @return array<string, mixed>|null
     * @throws Exception
     */
    public function findByGlossaryId(string $glossaryId): ?array
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $result = $connection->select(
            ['*'],
            'tx_wvdeepltranslate_glossary',
            ['glossary_id' => $glossaryId],
            [],
            [],
            1
        );

        return $result->fetchAssociative() ?: null;
    }

    public function updateLocalGlossary(GlossaryInfo $information, int $uid): void
    {
        $insertParams = [
            'glossary_id' => $information->glossaryId,
            'glossary_ready' => $information->ready ? 1 : 0,
            'glossary_lastsync' => $information->creationTime->getTimestamp(),
        ];

        $connection = $this->connectionPool->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $connection->update(
            'tx_wvdeepltranslate_glossary',
            $insertParams,
            [
                'uid' => $uid,
            ]
        );
    }

    /**
     * @return array<int|string, mixed>
     * @throws Exception
     */
    public function findAllGlossaries(): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');

        $identifiers = [
            'module' => 'glossary',
            'doktype' => PageRepository::DOKTYPE_SYSFOLDER,
            'sys_language_uid' => 0,
        ];

        return $connection->select(
            ['uid'],
            'pages',
            $identifiers
        )->fetchAllAssociative() ?: [];
    }

    /**
     * @param array{uid: int, title: string}|array<empty> $page
     * @return array{
     *     uid: int,
     *     glossary_name: string,
     *     glossary_id: string,
     *     glossary_lastsync: int,
     *     glossary_ready: int
     * }
     *
     * @throws Exception
     * @throws SiteNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function getGlossaryBySourceAndTarget(
        string $sourceLanguage,
        string $targetLanguage,
        array $page
    ): array {
        $defaultGlossary = [
            'uid' => 0,
            'glossary_id' => '',
            'glossary_name' => 'UNDEFINED',
            'glossary_lastsync' => 0,
            'glossary_ready' => 0,
        ];

        if (empty($page)) {
            return $defaultGlossary;
        }

        $lowerSourceLang = strtolower($sourceLanguage);
        $lowerTargetLang = strtolower($targetLanguage);

        if (strlen($lowerTargetLang) > 2) {
            $lowerTargetLang = substr($lowerTargetLang, 0, 2);
        }

        return $this->getGlossary(
            $lowerSourceLang,
            $lowerTargetLang,
            $page['uid'],
            true
        ) ?? $defaultGlossary;
    }

    /**
     * @param array{uid: int, title: string} $page
     * @return array{
     *     uid: int,
     *     glossary_name: string,
     *     glossary_id: string,
     *     glossary_lastsync: int,
     *     glossary_ready: int
     * }
     * @throws Exception
     * @throws SiteNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function getGlossaryBySourceAndTargetForSync(
        string $sourceLanguage,
        string $targetLanguage,
        array $page
    ): array {
        $lowerSourceLang = strtolower($sourceLanguage);
        $lowerTargetLang = strtolower($targetLanguage);
        if (strlen($lowerTargetLang) > 2) {
            $lowerTargetLang = substr($lowerTargetLang, 0, 2);
        }

        $result = $this->getGlossary($lowerSourceLang, $lowerTargetLang, $page['uid']);

        if ($result === null) {
            $insert = [
                'glossary_name' => sprintf(
                    '%s: %s => %s',
                    $page['title'],
                    $sourceLanguage,
                    $targetLanguage
                ),
                'glossary_id' => '',
                'glossary_lastsync' => 0,
                'glossary_ready' => 0,
                'source_lang' => $lowerSourceLang,
                'target_lang' => $lowerTargetLang,
                'pid' => $page['uid'],
            ];
            $connection = $this->connectionPool->getConnectionForTable('tx_wvdeepltranslate_glossary');
            $connection->insert('tx_wvdeepltranslate_glossary', $insert);
            $lastInsertId = $connection->lastInsertId('tx_wvdeepltranslate_glossary');
            $insert['uid'] = $lastInsertId;
            unset($insert['pid']);
            return $insert;
        }

        return $result;
    }

    public function removeGlossarySync(string $glossaryId): bool
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $count = $connection->update(
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

    /**
     * @return array<int|string, array{uid: int, glossary_id: string}>
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws DBALException
     */
    public function getGlossariesDeeplConnected(): array
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('tx_wvdeepltranslate_glossary');
        $statement = $queryBuilder
            ->select('uid', 'glossary_id')
            ->from('tx_wvdeepltranslate_glossary')
            ->where(
                $queryBuilder->expr()->neq('glossary_id', $queryBuilder->createNamedParameter(''))
            );

        $result = $statement->executeQuery()->fetchAssociative();
        if ($result === false) {
            return [];
        }

        return $result;
    }

    /**
     * @return array<int, array{uid: int, term: string}>|array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws DBALException
     */
    public function getOriginalEntries(int $pageId): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_wvdeepltranslate_glossaryentry');
        $statement = $queryBuilder
            ->select('uid', 'term')
            ->from('tx_wvdeepltranslate_glossaryentry')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pageId, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        $entries = [];
        foreach ($statement->executeQuery()->fetchAllAssociative() ?: [] as $entry) {
            $entries[$entry['uid']] = $entry;
        }

        return $entries;
    }

    /**
     * @return array<int, array{uid: int, term: string, l10n_parent: int}>
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws DBALException
     */
    public function getLocalizedEntries(int $pageId, int $languageId): array
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('tx_wvdeepltranslate_glossaryentry');
        $statement = $queryBuilder
            ->select('uid', 'term', 'l10n_parent')
            ->from('tx_wvdeepltranslate_glossaryentry')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pageId, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageId, Connection::PARAM_INT)
                )
            );

        $localizedEntries = [];
        foreach ($statement->executeQuery()->fetchAllAssociative() as $localizedEntry) {
            $localizedEntries[$localizedEntry['l10n_parent']] = $localizedEntry;
        }

        return $localizedEntries;
    }

    /**
     * @return array{
     *     uid: int,
     *     glossary_name: string,
     *     glossary_id: string,
     *     glossary_lastsync: int,
     *     glossary_ready: int
     * }|null
     * @throws SiteNotFoundException
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getGlossary(
        string $sourceLanguage,
        string $targetLanguage,
        int $pageUid,
        bool $recursive = false
    ): ?array {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_wvdeepltranslate_glossary');

        $constrains = [
            $queryBuilder->expr()->eq('source_lang', $queryBuilder->createNamedParameter($sourceLanguage)),
            $queryBuilder->expr()->eq('target_lang', $queryBuilder->createNamedParameter($targetLanguage)),
        ];

        if ($recursive === true) {
            $glossaryPages = $this->getGlossariesInRootByCurrentPage($pageUid);
            if (count($glossaryPages) > 0) {
                $constrains[] = $queryBuilder->expr()->in('pid', $glossaryPages);
            }
        } else {
            $constrains[] = $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT));
        }

        $statement = $queryBuilder->select(
            'uid',
            'glossary_id',
            'glossary_name',
            'glossary_lastsync',
            'glossary_ready',
        )
            ->from('tx_wvdeepltranslate_glossary')
            ->where(...$constrains);

        return $statement->executeQuery()->fetchAssociative() ?: null;
    }

    /**
     * @throws SiteNotFoundException
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getGlossariesInRootByCurrentPage(int $pageId): array
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByPageId($pageId);
        $rootPage = $site->getRootPageId();

        $allPages = GeneralUtility::makeInstance(PageTreeRepository::class)
            ->getTreeList($rootPage, 999);

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

        $statement = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in('uid', $allPages),
                $queryBuilder->expr()->eq('doktype', $queryBuilder->createNamedParameter(PageRepository::DOKTYPE_SYSFOLDER, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('module', $queryBuilder->createNamedParameter('glossary'))
            );
        $result = $statement->executeQuery()->fetchAllAssociative();

        if (!is_array($result)) {
            return [];
        }
        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row['uid'];
        }
        return $ids;
    }

    public function setGlossaryNotSyncOnPage(int $pageId): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_wvdeepltranslate_glossary');

        $queryBuilder->update('tx_wvdeepltranslate_glossary')
            ->set('glossary_ready', 0)
            ->where(
                $queryBuilder->expr()->eq('pid', $pageId)
            )->executeStatement();
    }
}
