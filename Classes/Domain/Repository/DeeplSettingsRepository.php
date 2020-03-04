<?php
namespace WebVision\WvDeepltranslate\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Ricky Mathew <ricky@web-vision.de>, web-vision GmbH
 *      Anu Bhuvanendran Nair <anu@web-vision.de>, web-vision GmbH
 *
 *  You may not remove or change the name of the author above. See:
 *  http://www.gnu.org/licenses/gpl-faq.html#IWantCredit
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DeeplSettingsRepository
 */

class DeeplSettingsRepository
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
        //$queryBuilder = $this->queryBuilder('tx_deepl_settings');
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
    public function getRecordField($table,$field,$recordData)
    {
        return $this->queryBuilder($table)
            ->select($field)
            ->from($table)
            ->where('deleted = 0 AND uid = ' . $recordData['uid'])
            ->execute()->fetchAll();
    }
}
