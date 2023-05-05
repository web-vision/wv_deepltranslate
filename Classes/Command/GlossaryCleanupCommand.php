<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Command;

use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Service\Client\DeepLException;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class GlossaryCleanupCommand extends Command
{
    protected DeeplGlossaryService $deeplGlossaryService;

    protected GlossaryRepository $glossaryRepository;
    public function __construct(
        string $name = null,
        ?DeeplGlossaryService $deeplGlossaryService = null,
        ?GlossaryRepository $glossaryRepository = null
    ) {
        parent::__construct($name);
        $this->deeplGlossaryService = $deeplGlossaryService ?? GeneralUtility::makeInstance(DeeplGlossaryService::class);
        $this->glossaryRepository = $glossaryRepository ?? GeneralUtility::makeInstance(GlossaryRepository::class);
    }

    protected function configure(): void
    {
        $this->setDescription('Cleanup Glossary entries in DeepL Database');
        $this->addOption(
            'yes',
            'y',
            InputOption::VALUE_NONE,
            'Force deletion without asking'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (empty($input->getOption('yes'))) {
            $io = new SymfonyStyle($input, $output);
            $yes = $io->ask('Really all delete? [yY]');
            if (strtolower($yes) !== 'y') {
                $output->writeln('Abort.');
                exit;
            }
            $input->setOption('yes', true);
        }
    }

    /**
     * @throws DeepLException
     * @throws DBALException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        if ($input->getOption('yes') === false) {
            $output->writeln('Deletion not confirmed. Cancel.');
            /**
             * return 2 HAS to be for TYPO3 v9 support
             * @see https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html#return-value
             */
            return 2;
        }

        $this->removeAllGlossaryEntries($output);
        $output->writeln('Success!');

        /**
         * return 0 HAS to be for TYPO3 v9 support
         * @see https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html#return-value
         */
        return 0;
    }

    /**
     * @throws DeepLException
     * @throws DBALException
     */
    private function removeAllGlossaryEntries(OutputInterface $output): void
    {
        $glossaries = $this->deeplGlossaryService->listGlossaries();

        if (empty($glossaries['glossaries'])) {
            $output->writeln('No glossaries found with sync to API');
            return;
        }

        $progress = new ProgressBar($output, count($glossaries['glossaries']));
        $progress->start();

        $removedGlossary = [];

        foreach ($glossaries['glossaries'] as $eachGlossary) {
            if (!isset($eachGlossary['glossary_id'])) {
                continue;
            }

            $id = $eachGlossary['glossary_id'];
            $this->deeplGlossaryService->deleteGlossary($id);
            $databaseUpdated = $this->glossaryRepository->removeGlossarySync($id);
            $removedGlossary[$id] = $databaseUpdated;
            $progress->advance();
        }

        $progress->finish();

        $table = new Table($output);

        $table->setHeaders([
            'Glossary ID',
            'Database sync removed',
        ]);
        foreach ($removedGlossary as $glossaryId => $dbUpdated) {
            $table->addRow([$glossaryId, $dbUpdated ? 'yes' : 'no']);
        }

        $output->writeln('');
        $table->render();
        $output->writeln('');

        $findNotConnected = $this->glossaryRepository->getGlossariesDeeplConnected();

        if (count($findNotConnected) === 0) {
            $output->writeln('No glossaries with sync mismatch.');
        }
        foreach ($findNotConnected as $notConnected) {
            $this->glossaryRepository->removeGlossarySync($notConnected['glossary_id']);
        }

        $output->writeln([
            sprintf('Found %d glossaries with possible sync mismatch. Cleaned up.', count($findNotConnected)),
        ]);
    }
}
