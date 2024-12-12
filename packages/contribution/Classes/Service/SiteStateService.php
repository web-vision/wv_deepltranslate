<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Contribution\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SiteStateService
{
    public function __construct(
        private SiteFinder $siteFinder,
        private ConnectionPool $connectionPool,
    ) {
    }

    public function enableSite(SymfonyStyle $io): void
    {
        $this->resetState();
        $allSites = $this->siteFinder->getAllSites(false);
        if ($allSites === []) {
            throw new \RuntimeException(
                sprintf('No sites found in %s', __METHOD__),
                1734010952,
            );
        }
        foreach ($allSites as $site) {
            $io->write(sprintf(
                'Enable site "%s"[RootPID: %s][URI: %s] ... ',
                $site->getIdentifier(),
                $site->getRootPageId(),
                $this->getSiteBaseUri($site),
            ));
            $record = $this->getRecord($site->getRootPageId());
            if ($record === null) {
                $io->writeln('<error>not found</error>');
                continue;
            }
            $this->updatePage($site->getRootPageId(), ['hidden' => 0]);
            $io->writeln('<info>enabled</info>');
        }
    }

    public function disableSite(SymfonyStyle $io): void
    {
        $this->resetState();
        $allSites = $this->siteFinder->getAllSites(false);
        if ($allSites === []) {
            throw new \RuntimeException(
                sprintf('No sites found in %s', __METHOD__),
                1734010933,
            );
        }
        foreach ($allSites as $site) {
            $io->write(sprintf(
                'Disable site "%s"[RootPID: %s][URI: %s] ... ',
                $site->getIdentifier(),
                $site->getRootPageId(),
                $this->getSiteBaseUri($site),
            ));
            $record = $this->getRecord($site->getRootPageId());
            if ($record === null) {
                $io->writeln('<error>not found</error>');
                continue;
            }
            $this->updatePage($site->getRootPageId(), ['hidden' => 1]);
            $io->writeln('<info>disabled</info>');
        }
    }

    private function getSiteBaseUri(Site $site): string
    {
        $primaryUrl = rtrim((string)(getenv('DDEV_PRIMARY_URL') ?: ''), '/');
        $baseUri = ltrim((string)$site->getBase(), '/');
        return ltrim($primaryUrl . '/' . $baseUri, '/');
    }

    public function getRecord(int $pageId): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        return $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageId, Connection::PARAM_INT)),
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    public function updatePage(int $pageId, array $data): void
    {
        $this->connectionPool->getConnectionForTable('pages')
            ->update('pages', $data, ['uid' => $pageId]);
    }

    private function resetState(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 13) {
            GeneralUtility::getContainer()->get('cache.core')->flush();
            GeneralUtility::getContainer()->get('cache.runtime')->flush();
            $this->siteFinder->getAllSites(false);
        } else {
            GeneralUtility::getContainer()->get('cache.core')->flush();
            $siteFinderReflection = new \ReflectionClass($this->siteFinder);
            $siteFinderReflection->getProperty('sites')->setValue($this->siteFinder, []);
            $this->siteFinder->getAllSites(false);
        }
    }
}
