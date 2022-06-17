<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class SettingsRepository extends Repository
{
    public function makeQueryBuilder(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    /**
     * @param array[] $data
     */
    public function insertDeeplSettings(array $data): void
    {
        $this->makeQueryBuilder('tx_deepl_settings')
            ->insert('tx_deepl_settings')
            ->values($data)
            ->execute();
    }

    /**
     * @param array{uid:int, languages_assigned:string} $data
     */
    public function updateDeeplSettings(array $data): void
    {
        $queryBuilder = $this->makeQueryBuilder('tx_deepl_settings');

        $queryBuilder->update('tx_deepl_settings')
            ->where(
                $queryBuilder->expr()->eq('uid', $data['uid'])
            )
            ->set('languages_assigned', $data['languages_assigned'])
            ->execute();
    }

    /**
     * Get assigned languages if any
     *
     * @return array{uid:int, pid:int, languages_assigned:string}
     */
    public function getAssignments(): array
    {
        $result = $this->makeQueryBuilder('tx_deepl_settings')
            ->select('*')
            ->from('tx_deepl_settings')
            ->execute();

        if ($result->rowCount() === 0) {
            return [];
        }

        return $result->fetch();
    }

    /**
     * Get language mappings for a sys_language
     *
     * @return string
     */
    public function getMappings(int $uid): string
    {
        $mappings = $this->getAssignments();

        if (empty($mappings) && empty($mappings['languages_assigned'])) {
            return '';
        }

        $assignments = unserialize($mappings['languages_assigned']);

        if (!isset($assignments[$uid])) {
            return '';
        }

        return $assignments[$uid];
    }

    /**
     * Merges default supported languages with language mappings
     *
     * @param array $apiSupportedLanguages
     * @return array
     */
    public function getSupportedLanguages(array $apiSupportedLanguages): array
    {
        $assignments = $this->getAssignments();

        if (empty($assignments)) {
            return $apiSupportedLanguages;
        }

        $languages = unserialize($assignments['languages_assigned']);

        foreach ($languages as $language) {
            if (!in_array($language, $apiSupportedLanguages)) {
                $apiSupportedLanguages[] = $language;
            }
        }

        return $apiSupportedLanguages;
    }

    /**
     * Return all sys languages
     *
     * @return array<array{uid: int, title: string, language_isocode: string}>
     */
    public function getSysLanguages(): array
    {
        $result = $this->makeQueryBuilder('sys_language')
            ->select('uid', 'title', 'language_isocode')
            ->from('sys_language')
            ->execute();

        if ($result->rowCount() === 0) {
            return [];
        }

        return $result->fetchAll();
    }

    /**
     * Get record field
     *
     * @param array<string> $fields
     * @return array[] array
     */
    public function getRecordField(string $table, array $fields, int $recordUid)
    {
        $queryBuilder = $this->makeQueryBuilder($table);
        $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);

        return $queryBuilder->select(...$fields)
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($recordUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchAll();
    }
}
