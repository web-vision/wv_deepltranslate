<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class GlossariessyncRepository extends Repository
{
    protected $defaultOrderings = [
        'crdate' => QueryInterface::ORDER_DESCENDING,
    ];

    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function truncateDbSyncRecords()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_wvdeepltranslate_domain_model_glossariessync');
        $connection->truncate('tx_wvdeepltranslate_domain_model_glossariessync');
    }

    /**
     * @param string $sourceLang
     * @param string $targetLang
     * @return string
     */
    public function getGlossaryIdByLanguages(string $sourceLang, string $targetLang): string
    {
        $constraints = [];

        $query = $this->createQuery();

        $constraints[] = $query->like('source_lang', $sourceLang);
        $constraints[] = $query->like('target_lang', $targetLang);

        $query->matching(
            $query->logicalAnd($constraints)
        );

        $glossaries = $query->execute();

        if ($glossaries->count() > 0) {
            $obj = $glossaries->getFirst();
            return $obj->getGlossaryId();
        }

        return '';
    }
}
