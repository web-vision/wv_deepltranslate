<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use WebVision\WvDeepltranslate\Domain\Model\Glossaries;

class GlossariesRepository extends Repository
{
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @return array
     */
    public function processGlossariesEntries(int $lang = 0): array
    {
        $entries = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_domain_model_glossaries');

        $result = $queryBuilder
            ->select('uid', 'term', 'l10n_parent')
            ->from('tx_wvdeepltranslate_domain_model_glossaries')
            ->where(
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($lang, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();

        foreach ($result as $record) {
            if ($record['l10n_parent'] > 0 && $lang > 0) {
                $parentUid = $record['l10n_parent'];
                $parent = $this->findByUid($parentUid);

                if ($parent instanceof Glossaries) {
                    $sourceTerm =  $parent->getTerm();
                    $targetTerm = $record['term'];
                    $entries[$sourceTerm] = $targetTerm;
                }
            }
        }

        return $entries;
    }
}
