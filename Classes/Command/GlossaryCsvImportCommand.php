<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use WebVision\WvDeepltranslate\Service\ImportGlossaryEntryService;

class GlossaryCsvImportCommand extends Command
{
    use GlossaryCommandTrait;

    protected ImportGlossaryEntryService  $importGlossaryEntryService;

    public function __construct(ImportGlossaryEntryService $importGlossaryEntryService, ?string $name = null)
    {
        $this->importGlossaryEntryService = $importGlossaryEntryService;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addOption(
                'pageId',
                'p',
                InputOption::VALUE_REQUIRED,
                'Page to import to',
                null
            )
            ->addOption(
                'csvFilePath',
                'f',
                InputOption::VALUE_REQUIRED,
                'combined file identifier [[storage uid]:]<file identifier> e.g. 1:/user_upload/csv_imports',
                null
            )
            ->addOption(
                'csvSeparator',
                's',
                InputOption::VALUE_OPTIONAL,
                'csv seperator default: ,',
                ','
            )
            ->addOption(
                'targetSysLanguage',
                't',
                InputOption::VALUE_REQUIRED,
                'The target language sys_language_uid',
                null
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Glossary Entry CSV Import');

        try {
            $glossaryEntries = $this->importGlossaryEntryService->getGlossaryEntriesFromCsv(
                (string) $input->getOption('csvFilePath'),
                (string) $input->getOption('csvSeparator')
            );

            $this->importGlossaryEntryService->insertEntriesLocal(
                $glossaryEntries,
                (int) $input->getOption('pageId'),
                (int) $input->getOption('targetSysLanguage')
            );

            $this->deeplGlossaryService->syncGlossaries((int)$input->getOption('pageId'));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
