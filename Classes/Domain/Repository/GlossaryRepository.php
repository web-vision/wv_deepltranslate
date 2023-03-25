<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Service\Client\DeepLException;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class GlossaryRepository
{
    /**
     * @param int $pageId
     * @return array<int, array{
     *     glossary_name: string,
     *     uid: int,
     *     glossary_id: string,
     *     source_lang: string,
     *     target_lang: string,
     *     entries: array<int, array{source: string, target: string}>
     * }>
     * @throws SiteNotFoundException
     * @throws DeepLException
     */
    public function getGlossaryInformationForSync(int $pageId): array
    {
        $glossaries = [];
        $localizationArray = [];

        $page = BackendUtility::getRecord(
            'pages',
            $pageId
        );
        $entries = $this->getOriginalEntries($pageId);
        $localizationLanguageIds = $this->getAvailableLocalizations($pageId);
        $site = GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByPageId($pageId);
        $sourceLangIsoCode = $site->getDefaultLanguage()->getTwoLetterIsoCode();

        $localizationArray[$sourceLangIsoCode] = $entries;

        // fetch all language information available for building all glossaries
        foreach ($localizationLanguageIds as $localizationLanguageId) {
            $localizedEntries = $this->getLocalizedEntries($pageId, $localizationLanguageId);
            $targetLanguageIsoCode = $this->getTargetLanguageIsoCode($site, $localizationLanguageId);
            $localizationArray[$targetLanguageIsoCode] = $localizedEntries;
        }

        $availableLanguagePairs = GeneralUtility::makeInstance(DeeplGlossaryService::class)
            ->getPossibleGlossaryLanguageConfig();

        foreach ($availableLanguagePairs as $sourceLang => $availableTargets) {
            // no entry to possible source in current page
            if (!isset($localizationArray[$sourceLang])) {
                continue;
            }

            foreach ($availableTargets as $targetLang) {
                // target not configured in current page
                if (!isset($localizationArray[$targetLang])) {
                    continue;
                }

                // target is site default, continue
                if ($targetLang === $sourceLangIsoCode) {
                    continue;
                }

                $glossaryInformation = $this->getGlossaryBySourceAndTarget(
                    $sourceLang,
                    $targetLang,
                    $page
                );
                $glossaryInformation['source_lang'] = $sourceLang;
                $glossaryInformation['target_lang'] = $targetLang;

                foreach ($localizationArray[$sourceLang] as $entryId => $sourceEntry) {
                    // no source target pair, next
                    if (!isset($localizationArray[$targetLang][$entryId])) {
                        continue;
                    }
                    $glossaryInformation['entries'][] = [
                        'source' => $sourceEntry['term'],
                        'target' => $localizationArray[$targetLang][$entryId]['term'],
                    ];
                }
                // no pairs detected
                if (count($glossaryInformation['entries']) == 0) {
                    continue;
                }
                $glossaries[] = $glossaryInformation;
            }
        }

        return $glossaries;
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

    public function findAllGlossaries(): array
    {
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');

        $identifiers = [
            'module' => 'glossary',
            'doktype' => 254,
        ];

        return $db->select(
            ['uid'],
            'pages',
            $identifiers
        )->fetchAll() ?: [];
    }

    /**
     * @return array{
     *     uid: int,
     *     glossary_name: string,
     *     glossary_id: string,
     *     glossary_lastsync: int,
     *     glossary_ready: int
     * }
     */
    public function getGlossaryBySourceAndTarget(
        string $sourceLanguage,
        string $targetLanguage,
        array $page
    ): array {
        $lowerSourceLang = strtolower($sourceLanguage);
        $lowerTargetLang = strtolower($targetLanguage);
        if (strlen($lowerTargetLang) > 2) {
            $lowerTargetLang = substr($lowerTargetLang, 0, 2);
        }

        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary');
        $where = [
            'source_lang' => $lowerSourceLang,
            'target_lang' => $lowerTargetLang,
            'pid' => $page['uid'],
        ];

        $statement = $db
            ->select(
                [
                    'uid',
                    'glossary_id',
                    'glossary_name',
                    'glossary_lastsync',
                    'glossary_ready',
                ],
                'tx_wvdeepltranslate_glossary',
                $where
            );
        $result = $statement->fetch();
        if ($result === false) {
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
            $db->insert('tx_wvdeepltranslate_glossary', $insert);
            $lastInsertId = $db->lastInsertId('tx_wvdeepltranslate_glossary');
            $insert['uid'] = $lastInsertId;
            unset($insert['pid']);
            return $insert;
        }

        return $result;
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

    /**
     * @return array<int|string, array{uid: int, glossary_id: string}>
     * @throws DBALException
     */
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

    /**
     * @return array<int, array{uid: int, term: string}>|array
     */
    private function getOriginalEntries(int $pageId): array
    {
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_glossaryentry');
        $statement = $db
            ->select('uid', 'term')
            ->from('tx_wvdeepltranslate_glossaryentry')
            ->where(
                $db->expr()->eq(
                    'pid',
                    $db->createNamedParameter($pageId, Connection::PARAM_INT)
                ),
                $db->expr()->eq(
                    'sys_language_uid',
                    $db->createNamedParameter(0, Connection::PARAM_INT)
                )
            );
        $entries = [];
        foreach ($statement->execute()->fetchAll() as $entry) {
            $entries[$entry['uid']] = $entry;
        }
        return $entries;
    }

    /**
     * @return array<int, array{uid: int, term: string, l10n_parent: int}>
     */
    private function getLocalizedEntries(int $pageId, int $languageId): array
    {
        $db = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_glossaryentry');
        $statement = $db
            ->select('uid', 'term', 'l10n_parent')
            ->from('tx_wvdeepltranslate_glossaryentry')
            ->where(
                $db->expr()->eq(
                    'pid',
                    $db->createNamedParameter($pageId, Connection::PARAM_INT)
                ),
                $db->expr()->eq(
                    'sys_language_uid',
                    $db->createNamedParameter($languageId, Connection::PARAM_INT)
                )
            );

        $localizedEntries = [];
        foreach ($statement->execute()->fetchAll() ?? [] as $localizedEntry) {
            $localizedEntries[$localizedEntry['l10n_parent']] = $localizedEntry;
        }
        return $localizedEntries;
    }

    /**
     * @return array<int, mixed>
     */
    private function getAvailableLocalizations(int $pageId): array
    {
        $translations = GeneralUtility::makeInstance(TranslationConfigurationProvider::class)
            ->translationInfo('pages', $pageId);

        $availableTranslations = [];
        var_dump($translations);
        foreach ($translations['translations'] as $translation) {
            $availableTranslations[] = $translation['sys_language_uid'];
        }

        return $availableTranslations;
    }

    protected function getTargetLanguageIsoCode(Site $site, int $languageId): string
    {
        // TODO add support for deprecated sys_language table
        return $site->getLanguageById($languageId)->getTwoLetterIsoCode();
    }
}
