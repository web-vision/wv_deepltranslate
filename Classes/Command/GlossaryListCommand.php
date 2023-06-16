<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Command;

use DeepL\DeepLException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class GlossaryListCommand extends Command
{
    protected DeeplGlossaryService $deeplGlossaryService;

    public function __construct(
        string $name,
        DeeplGlossaryService $deeplGlossaryService
    ) {
        parent::__construct($name);
        $this->deeplGlossaryService = $deeplGlossaryService;
    }
    protected function configure(): void
    {
        $this->setDescription('List Glossary entries or entries by glossary_id');
        $this->addArgument(
            'glossary_id',
            InputArgument::OPTIONAL,
            'Which glossary you want to fetch (id)?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $glossary_id = $input->getArgument('glossary_id');

        if ($glossary_id) {
            $this->listAllGloassaryEntriesById($output, $glossary_id);
            return 0;
        }

        $this->listAllGloassaryEntries($output);
        return Command::SUCCESS;
    }

    private function listAllGloassaryEntries(OutputInterface $output): void
    {
        $glossaries = $this->deeplGlossaryService->listGlossaries();

        if (empty($glossaries)) {
            $output->writeln([
                '============',
                'No Glossaries found.',
                'Read more here: https://www.deepl.com/docs-api/managing-glossaries/listing-glossaries/',
                '============',
            ]);

            return;
        }

        $output->writeln([
            '============',
            'Read more here: https://www.deepl.com/docs-api/managing-glossaries/listing-glossaries/',
            '============',
        ]);

        $headers = array_keys(get_object_vars($glossaries[0]));
        $rows = [];

        foreach ($glossaries as $eachGlossary) {
            $rows[] = $eachGlossary;
        }

        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }

    private function listAllGloassaryEntriesById(OutputInterface $output, string $id): void
    {
        $information = $this->deeplGlossaryService->glossaryInformation($id);
        $entries = $this->deeplGlossaryService->glossaryEntries($id);

        if ($information === null || $entries === null) {
            $output->writeln(
                [
                    'Glossary not found.',
                ]
            );
            return;
        }
        $output->writeln([
            '============',
            'List of Glossary entries',
            '============',
        ]);

        $headers = [
            'source_lang - ' . $information->sourceLang,
            'target_lang - ' . $information->targetLang,
        ];

        $rows = array_map(null, array_keys($entries->getEntries()), $entries->getEntries());

        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }
}
