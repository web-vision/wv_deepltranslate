<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class SettingsRepository extends Repository
{
    /**
     * queryBuilder
     * @param string $table
     * @return type
     */
    public function queryBuilder($table)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    /**
     * Description
     * @param type $data
     * @return type
     */
    public function insertDeeplSettings($data)
    {
        $this->queryBuilder('tx_deepl_settings')
            ->insert('tx_deepl_settings')
            ->values($data)
            ->execute();
    }
    /**
     * Description
     * @param type $data
     * @return type
     */
    public function updateDeeplSettings($data)
    {
        $queryBuilder = $this->queryBuilder('tx_deepl_settings');
        $this->queryBuilder('tx_deepl_settings')
            ->update('tx_deepl_settings')
            ->where(
                $queryBuilder->expr()->eq('uid', $data['uid'])
            )
            ->set('languages_assigned', $data['languages_assigned'])
            ->execute();
    }

    /**
     * get assigned languages if any
     * @param type $queryBuilder
     * @return array
     */
    public function getAssignments()
    {
        return $this->queryBuilder('tx_deepl_settings')->select('*')
            ->from('tx_deepl_settings')
            ->execute()
            ->fetchAll();
    }

    /**
     * get language mappings for a syslanguage
     * @return string
     */
    public function getMappings($uid)
    {
        $mappings = $this->queryBuilder('tx_deepl_settings')->select('*')
            ->from('tx_deepl_settings')
            ->execute()
            ->fetchAll();
        if (!empty($mappings) && !empty($mappings[0]['languages_assigned'])) {
            $assignments = unserialize($mappings[0]['languages_assigned']);
            if (isset($assignments[$uid]) && !empty($assignments[$uid])) {
                return $assignments[$uid];
            }
        }
    }

    /**
     * merges default supported languages with language mappings
     * @param array $apiSupportedLanguages
     * @return array $apiSupportedLanguages
     */
    public function getSupportedLanguages($apiSupportedLanguages)
    {
        $assignments = $this->getAssignments();
        if (!empty($assignments) && $assignments[0]['languages_assigned'] != '') {
            $languages = unserialize($assignments[0]['languages_assigned']);
            foreach ($languages as $language) {
                if (!in_array($language, $apiSupportedLanguages)) {
                    $apiSupportedLanguages[] = $language;
                }
            }
        }
        return $apiSupportedLanguages;
    }

    /**
     * return all sys languages
     * @return array
     */
    public function getSysLanguages()
    {
        return $this->queryBuilder('sys_language')->select('uid', 'title', 'language_isocode')
            ->from('sys_language')
            ->execute()
            ->fetchAll();
    }

    /**
     * get record field
     * @param type $table
     * @param type $field
     * @param type $recordData
     * @return type array
     */
    public function getRecordField($table, $field, $recordData)
    {
        return $this->queryBuilder($table)
            ->select($field)
            ->from($table)
            ->where('deleted = 0 AND uid = ' . $recordData['uid'])
            ->execute()->fetchAll();
    }
}
