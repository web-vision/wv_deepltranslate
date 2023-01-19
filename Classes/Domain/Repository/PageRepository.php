<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class PageRepository extends Repository
{
    private const TABLE_NAME = 'pages';

    private ConnectionPool $connectionPool;

    public function __construct()
    {
        $this->connectionPool = new ConnectionPool();
    }

    public function hasDeeplTranslatedContent()
    {
        $result = $this->connectionPool
            ->getConnectionForTable(self::TABLE_NAME)
            ->select(
                ['uid', 'tx_wvdeepltranslate_has_translated_content'],
                self::TABLE_NAME,
                ['uid' => $this->getCurrentBackendPageId()]
            )->fetchAssociative();

        $deeplTrasalatedTime = $result['tx_wvdeepltranslate_has_translated_content'];
        return $deeplTrasalatedTime > 0 ? true : false;
    }

    protected function getCurrentBackendPageId()
    {
        $pageId = null;
        if ($GLOBALS['TYPO3_REQUEST']) {
            $queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams();
            $pageId = (function () use ($queryParams) {
                if (isset($queryParams['edit']['pages'])) {
                    $pages = array_keys($queryParams['edit']['pages']);
                    return $pages[0];
                }
            })();
        }
        //need more implementation

        return $pageId;
    }

    public function setAsTranslatedWithDeepl($pageId)
    {
        $this->connectionPool
        ->getConnectionForTable('pages')
        ->update(
            'pages',
            [
                'tx_wvdeepltranslate_has_translated_content' => 1,
                'tx_wvdeepltranslate_translated_time' => time(),
            ],
            ['uid' => (int)$pageId],
            [Connection::PARAM_INT]
        );
    }
}
