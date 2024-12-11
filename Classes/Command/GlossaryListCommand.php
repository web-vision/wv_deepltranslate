<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Command;

use DateTime;
use DeepL\GlossaryInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GlossaryListCommand extends Command
{
    use GlossaryCommandTrait;

    private SymfonyStyle $io;

    protected function configure(): void
    {
        $this->addArgument(
            'glossary_id',
            InputArgument::OPTIONAL,
            'Which glossary you want to fetch (id)?',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Glossary List');

        $glossary_id = $input->getArgument('glossary_id');

        if ($glossary_id !== null) {
            $this->listAllGlossaryEntriesById($glossary_id);
        } else {
            $this->listAllGlossaryEntries();
        }

        return Command::SUCCESS;
    }

    private function listAllGlossaryEntries(): void
    {
        $glossaries = $this->deeplGlossaryService->listGlossaries();

        $this->io->writeln('Read more here: https://www.deepl.com/docs-api/managing-glossaries/listing-glossaries/');
        $this->io->newLine();
        if ($glossaries === []) {
            $this->io->info('No Glossaries found.');
            return;
        }

        $headers = [
            'Glossary ID',
            'Name',
            'Ready',
            'Source Language',
            'Target Language',
            'Creation Time',
            'Entry count',
        ];

        $rows = array_map(function (GlossaryInfo $glossary) {
            return [
                'glossaryId' => $glossary->glossaryId,
                'name' => $glossary->name,
                'ready' => $glossary->ready,
                'sourceLang' => $glossary->sourceLang,
                'targetLang' => $glossary->targetLang,
                'creationTime' => $glossary->creationTime->format(DateTime::ATOM),
                'entryCount' => $glossary->entryCount,
            ];
        }, $glossaries);

        $this->io->table($headers, $rows);
    }

    private function listAllGlossaryEntriesById(string $id): void
    {
        $glossaryInformation = $this->deeplGlossaryService->glossaryInformation($id);
        if ($glossaryInformation === null) {
            $this->io->warning(sprintf('Glossary "%s" not found.', $id));
            return;
        }
        if ($glossaryInformation->entryCount === 0) {
            $this->io->warning(sprintf('Glossary "%s" has no entries.', $id));
            return;
        }
        $entries = $this->deeplGlossaryService->glossaryEntries($id);
        if ($entries === null) {
            $this->io->warning('The API giv entries.');
            return;
        }

        $this->io->writeln([
            sprintf('Glossary entries from: %s', $glossaryInformation->glossaryId),
            sprintf('Entries count: %s', $glossaryInformation->entryCount),
            sprintf('Is ready: %s', $glossaryInformation->ready ? 'yes' : 'no'),
            sprintf('Creation Time: %s', $glossaryInformation->creationTime->format(DateTime::ATOM)),
        ]);
        $this->io->newLine();

        $rows = array_map(null, array_keys($entries->getEntries()), $entries->getEntries());
        $this->io->table(
            [
                'source_lang: ' . $glossaryInformation->sourceLang,
                'target_lang:' . $glossaryInformation->targetLang,
            ],
            $rows
        );
    }
}
