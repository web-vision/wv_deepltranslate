<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Command;

use DeepL\GlossaryInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * ToDo: Rename Command
 * ToDo: Split command in housekeeping and remove glossary from API/remote storage
 */
class GlossaryCleanupCommand extends Command
{
    use GlossaryCommandTrait;

    private SymfonyStyle $io;

    protected function configure(): void
    {
        $this
            ->addOption(
                'glossaryId',
                null,
                InputOption::VALUE_OPTIONAL,
                'Deleted single Glossary',
                null
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Deleted all Glossaries',
            )
            ->addOption(
                'notinsync',
                null,
                InputOption::VALUE_NONE,
                'Deleted all Glossaries without synchronization information',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Glossary cleanup');

        $question = new ConfirmationQuestion(
            'Do you will execute the glossary cleanup?',
            false,
            '/^(y|j)/i'
        );

        if (!$this->io->askQuestion($question)) {
            $this->io->writeln('<warning>Delete not confirmed, process was cancel.</warning>');
            return Command::SUCCESS;
        }

        // Remove single glossary by deepl-id
        $glossaryId = $input->getOption('glossaryId');
        if ($glossaryId !== null) {
            $this->removeGlossaries($glossaryId);
        }
        // Remove all glossaries
        if (!empty($input->getOption('all'))) {
            $glossaries = $this->deeplGlossaryService->listGlossaries();
            if (empty($glossaries)) {
                $this->io->writeln('No glossaries found with sync to API');
                return Command::FAILURE;
            }

            $this->removeGlossaries($glossaries);
        }
        // Remove glossaries without api sync id
        if (!empty($input->getOption('notinsync'))) {
            $this->removeGlossariesWithNoSync();
        }

        $this->io->writeln('Success!');

        return Command::SUCCESS;
    }

    private function removeGlossary(string $id): bool
    {
        $this->deeplGlossaryService->deleteGlossary($id);
        return $this->glossaryRepository->removeGlossarySync($id);
    }

    /**
     * @param GlossaryInfo[] $glossaries
     */
    private function removeGlossaries(array $glossaries): void
    {
        $rows = [];
        $this->io->progressStart(count($glossaries));

        foreach ($glossaries as $glossary) {
            $dbUpdated = $this->removeGlossary($glossary->glossaryId);
            $rows[] = [$glossary->glossaryId, $dbUpdated ? 'yes' : 'no'];
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->table(
            [
                'Glossary ID',
                'Database sync removed',
            ],
            $rows
        );
    }

    private function removeGlossariesWithNoSync(): void
    {
        $findNotConnected = $this->glossaryRepository->getGlossariesDeeplConnected();

        if (count($findNotConnected) === 0) {
            $this->io->writeln('No glossaries with sync mismatch.');
        }

        $this->io->progressStart(count($findNotConnected));
        foreach ($findNotConnected as $notConnected) {
            $this->glossaryRepository->removeGlossarySync($notConnected['glossary_id']);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        $this->io->writeln(
            sprintf('Found %d glossaries with possible sync mismatch. Cleaned up.', count($findNotConnected))
        );
    }
}
