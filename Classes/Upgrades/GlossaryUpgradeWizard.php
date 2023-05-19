<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Upgrades;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class GlossaryUpgradeWizard implements UpgradeWizardInterface, ChattyInterface
{
    protected OutputInterface $output;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'wvDeepltranslate_updateGlossary';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return 'Update glossary';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Updates local glossaries to cleaned up structure. Migrates tables and fixes be_groups';
    }

    /**
     * @inheritDoc
     * @throws DBALException
     */
    public function executeUpdate(): bool
    {
        $this->output->writeln('<info>Preparing migration of entries</info>');

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_domain_model_glossaries');
        $connection->getRestrictions()->removeAll();
        $result = $connection
            ->select('*')
            ->from('tx_wvdeepltranslate_domain_model_glossaries')
            ->executeQuery()
            ->fetchAllAssociative();

        $updateGlossary = [];
        foreach ($result as $item) {
            unset($item['description']);
            unset($item['starttime']);
            unset($item['endtime']);
            $updateGlossary[] = $item;
        }

        $insert = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossaryentry');
        $inserted = $insert->bulkInsert(
            'tx_wvdeepltranslate_glossaryentry',
            $updateGlossary,
            array_keys($updateGlossary[0])
        );

        $this->output->writeln(sprintf('<info>Migrated %d entries</info>', $inserted));

        $this->output->writeln('<info>Preparing migration of glossaries</info>');
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_domain_model_glossariessync');
        $connection->getRestrictions()->removeAll();
        $result = $connection
            ->select('*')
            ->from('tx_wvdeepltranslate_domain_model_glossariessync')
            ->executeQuery()
            ->fetchAllAssociative();

        $updateGlossary = [];
        foreach ($result as $item) {
            unset($item['entries']);
            $updateGlossary[] = $item;
        }

        $insert = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_wvdeepltranslate_glossary');

        $inserted = $insert->bulkInsert(
            'tx_wvdeepltranslate_glossary',
            $updateGlossary,
            array_keys($updateGlossary[0])
        );

        $this->output->writeln(sprintf('<info>Migrated %d glossaries</info>', $inserted));
        $this->output->writeln('<info>Preparing backend access rights migration</info>');

        $db = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_groups');

        // TYPO3 does not add a proper "ESCAPE ''" suffix to like statements. This worked a long time, but for some
        // database version and systems, it does no longer work. Until TYPO3 adds an option for the escaped value or
        // autoset it, we need to handle this by our own.
        $typo3Version = new Typo3Version();
        $dbPlatform = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('be_groups')->getDatabasePlatform();
        $escapeFixNeeded = (
            // TYPO3 uses ILIKE for like()/notLike() expression, and adding ESCAPE for default '\'  is disliked. Avoid it.
            (! $dbPlatform instanceof PostgreSQL94Platform)
            && (
                (($typo3Version->getMajorVersion() === 11) && (version_compare($typo3Version->getVersion(), '11.5.27', '<=') === true))
             || (($typo3Version->getMajorVersion() === 12) && (version_compare($typo3Version->getVersion(), '12.4.1', '<=') === true))
            )
        );
        $escapeSuffix = $escapeFixNeeded ? sprintf(' ESCAPE %s', $db->quote('\\')) : '';
        $v1 = $db->escapeLikeWildcards('tx_wvdeepltranslate_domain_model_glossariessync');
        $v2 = $db->escapeLikeWildcards('tx_wvdeepltranslate_domain_model_glossaries');

        $statement = $db
            ->select('*')
            ->from('be_groups')
            ->where(
                $db->expr()->or(
                    $db->expr()->like(
                        'be_groups.tables_modify',
                        $db->createNamedParameter('%' . $v1 . '%') . $escapeSuffix
                    ),
                    $db->expr()->like(
                        'be_groups.tables_modify',
                        $db->createNamedParameter('%' . $v2 . '%') . $escapeSuffix
                    ),
                    $db->expr()->like(
                        'be_groups.tables_select',
                        $db->createNamedParameter('%' . $v1 . '%') . $escapeSuffix
                    ),
                    $db->expr()->like(
                        'be_groups.tables_select',
                        $db->createNamedParameter('%' . $v2 . '%') . $escapeSuffix
                    )
                )
            );

        $result = $statement->executeQuery()->fetchAllAssociative();
        $countBeGroups = 0;
        foreach ($result as $group) {
            $replaced = false;
            $selectTables = GeneralUtility::trimExplode(',', $group['tables_select']);
            $glossaryKey = array_search('tx_wvdeepltranslate_domain_model_glossaries', $selectTables);
            $syncKey = array_search('tx_wvdeepltranslate_domain_model_glossariessync', $selectTables);

            if ($glossaryKey !== false) {
                $selectTables[$glossaryKey] = 'tx_wvdeepltranslate_glossaryentry';
                $replaced = true;
            }

            if ($syncKey !== false) {
                $selectTables[$syncKey] = 'tx_wvdeepltranslate_glossary';
                $replaced = true;
            }

            $modifyTables = GeneralUtility::trimExplode(',', $group['tables_modify']);
            $glossaryKey = array_search('tx_wvdeepltranslate_domain_model_glossaries', $modifyTables);
            $syncKey = array_search('tx_wvdeepltranslate_domain_model_glossariessync', $modifyTables);

            if ($glossaryKey !== false) {
                $modifyTables[$glossaryKey] = 'tx_wvdeepltranslate_glossaryentry';
                $replaced = true;
            }

            if ($syncKey !== false) {
                $modifyTables[$syncKey] = 'tx_wvdeepltranslate_glossary';
                $replaced = true;
            }

            if ($replaced === true) {
                $update = [
                    'tables_select' => implode(',', $selectTables),
                    'tables_modify' => implode(',', $modifyTables),
                ];
                $updateDb = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable('be_groups');
                $updateDb->update(
                    'be_groups',
                    $update,
                    [
                        'uid' => $group['uid'],
                    ]
                );
                $countBeGroups++;
            }
        }

        $this->output->writeln(sprintf('<info>Updated %d backend groups</info>', $countBeGroups));

        $pagesQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $pagesModuleResult = $pagesQueryBuilder
            ->update('pages')
            ->set('module', 'glossary')
            ->where(
                $pagesQueryBuilder->expr()->eq('doktype', 254),
                $pagesQueryBuilder->expr()->eq('module', $pagesQueryBuilder->createNamedParameter('wv_deepltranslate'))
            )
            ->executeStatement();

        $this->output->writeln(sprintf('<info>Update %d sys-folder module</info>', (int)$pagesModuleResult));

        $this->output->writeln('<info>All migrations done.</info>');
        $this->output->writeln('<comment>You should run `typo3 deepl:glossary:sync` for receiving all glossary information.</comment>');
        $this->output->writeln('<comment>You should run database update wizard and remove old tables.</comment>');

        return true;
    }

    /**
     * @inheritDoc
     * @throws DBALException
     */
    public function updateNecessary(): bool
    {
        // Check table to migrate is exist
        $schemaManger = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->getSchemaManager();

        if (!$schemaManger->tablesExist('tx_wvdeepltranslate_domain_model_glossaries')
            || !$schemaManger->tablesExist('tx_wvdeepltranslate_domain_model_glossariessync')
        ) {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_wvdeepltranslate_domain_model_glossaries');
        $queryBuilder->getRestrictions()->removeAll();
        $count = (int)$queryBuilder
            ->count('*')
            ->from('tx_wvdeepltranslate_domain_model_glossaries')
            ->executeQuery()
            ->fetchOne();

        return $count > 0;
    }

    /**
     * @inheritDoc
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
