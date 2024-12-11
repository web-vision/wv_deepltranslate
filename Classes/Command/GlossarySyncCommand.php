<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GlossarySyncCommand extends Command
{
    use GlossaryCommandTrait;

    private SymfonyStyle $io;

    protected function configure(): void
    {
        $this
            ->addOption(
                'pageId',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Page to sync, not set, sync all glossaries',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Glossary Sync');

        try {
            $pageId = $input->getOption('pageId');
            if ($pageId !== null) {
                $glossaries[] = ['uid' => (int)$pageId];
            } else {
                $glossaries = $this->glossaryRepository->findAllGlossaries();
            }

            $this->io->progressStart(count($glossaries));
            foreach ($glossaries as $glossary) {
                $this->deeplGlossaryService->syncGlossaries($glossary['uid']);
                $this->io->progressAdvance();
            }
            $this->io->progressFinish();
        } catch (Exception $exception) {
            $this->io->error(sprintf('%s (%s)', $exception->getMessage(), $exception->getCode()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
