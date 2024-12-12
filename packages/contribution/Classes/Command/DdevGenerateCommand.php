<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Contribution\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\Deepltranslate\Contribution\Service\SiteStateService;

final class DdevGenerateCommand extends Command
{
    public function __construct(
        private SiteStateService $siteStateService,
        private CacheManager $cacheManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Make sure the _cli_ user is loaded
        Bootstrap::initializeBackendAuthentication();
        $io = new SymfonyStyle($input, $output);

        $statusGenerateFrontendTree = $this->generateStyleGuideFrontendTree($io, $output);
        if ($statusGenerateFrontendTree !== Command::SUCCESS) {
            $io->error('!!! Failed !!!');
            return $statusGenerateFrontendTree;
        }

        $this->resetState();

        $enableSite = $this->enableSite($io);
        if ($enableSite !== Command::SUCCESS) {
            return $enableSite;
        }

        $io->success('Success ;)');
        return Command::SUCCESS;
    }

    private function generateStyleGuideFrontendTree(SymfonyStyle $io, OutputInterface $output): int
    {
        $type = ((new Typo3Version())->getMajorVersion() >= 13) ? 'frontend-systemplate' : 'frontend';
        $io->writeln(sprintf('>> styleguide:generate --create %s', $type));
        return $this->dispatchSubCommand(
            $output,
            'styleguide:generate',
            [
                '--create' => true,
                'type' => $type,
            ],
        );
    }

    private function resetState(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 13) {
            GeneralUtility::getContainer()->get('cache.core')->flush();
            GeneralUtility::getContainer()->get('cache.runtime')->flush();
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $siteFinder->getAllSites(false);
        } else {
            GeneralUtility::getContainer()->get('cache.core')->flush();
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $siteFinderReflection = new \ReflectionClass($siteFinder);
            $siteFinderReflection->getProperty('sites')->setValue($siteFinder, []);
            $siteFinder->getAllSites(false);
        }
    }

    private function enableSite(SymfonyStyle $io): int
    {
        try {
            $this->siteStateService->enableSite($io);
            return Command::SUCCESS;
        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return Command::FAILURE;
        }
    }

    private function dispatchSubCommand(
        OutputInterface $output,
        string $command,
        array $parameters = [],
    ): int {
        $options = [
            'command' => $command,
        ];
        $options = array_merge($options, $parameters);
        $input = new ArrayInput($options);
        $input->setInteractive(false);
        return $this->getApplication()->doRun($input, $output);
    }
}
