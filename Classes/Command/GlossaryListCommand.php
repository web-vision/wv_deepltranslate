<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class GlossaryListCommand extends Command
{
    protected DeeplGlossaryService $deeplGlossaryService;

    public function __construct(
        string $name = null,
        ?DeeplGlossaryService $deeplGlossaryService = null
    ) {
        parent::__construct($name);
        $this->deeplGlossaryService = $deeplGlossaryService
            ?? GeneralUtility::makeInstance(DeeplGlossaryService::class);
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

        /**
         * return 0 HAS to be for TYPO3 v9 support
         * @see https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html#return-value
         */
        $this->listAllGloassaryEntries($output);
        return 0;
    }

    private function listAllGloassaryEntries(OutputInterface $output): void
    {
        $glossaries = $this->deeplGlossaryService->listGlossaries();

        if (empty($glossaries['glossaries'])) {
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

        $headers = array_keys($glossaries['glossaries'][0]);
        $rows = [];

        foreach ($glossaries['glossaries'] as $eachGlossary) {
            $rows[] = $eachGlossary;
        }

        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }

    private function listAllGloassaryEntriesById(OutputInterface $output, $id): void
    {
        $entries = $this->deeplGlossaryService->glossaryEntries($id);
        $information = $this->deeplGlossaryService->glossaryInformation($id);

        $output->writeln([
            '============',
            'List of Glossary entries',
            '============',
        ]);

        $headers = [
            'source_lang - ' . $information['source_lang'],
            'target_lang - ' . $information['target_lang'],
        ];

        $rows = array_map(null, array_keys($entries), $entries);

        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }
}
