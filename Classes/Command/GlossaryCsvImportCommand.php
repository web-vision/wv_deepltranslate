<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        Bootstrap::initializeBackendAuthentication();
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
                'The path to the csv file',
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

        $pageId = (int) $input->getOption('pageId');
        $sysLanguageUid = (int) $input->getOption('targetSysLanguage');

        if ($this->translationExistsForPage($pageId, $sysLanguageUid) === false) {
            $io->error(
                'Page with uid: "' . $pageId . '" has no translation for sys_language_uid: "' . $sysLanguageUid .
                '". Create a translation for this page first'
            );

            return Command::FAILURE;
        }

        try {
            $glossaryEntries = $this->importGlossaryEntryService->getGlossaryEntriesFromCsv(
                (string) $input->getOption('csvFilePath'),
                (string) $input->getOption('csvSeparator'),
                $pageId
            );

            $this->importGlossaryEntryService->insertEntriesLocal($glossaryEntries, $pageId, $sysLanguageUid);
            $this->outputFailures($io);

            $this->deeplGlossaryService->syncGlossaries($pageId);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function outputFailures(SymfonyStyle $symfonyStyle): void
    {
        $allEntriesCount = count($this->importGlossaryEntryService->getAllEntries());
        $successCount = $allEntriesCount - $this->importGlossaryEntryService->getFailuresCount();

        foreach ($this->importGlossaryEntryService->getDataHandlerErrors() as $dataHandlerError) {
            $symfonyStyle->error('Error while inserting data:' . $dataHandlerError);
        }

        foreach (
            $this->importGlossaryEntryService->getFailedEntries(ImportGlossaryEntryService::ERROR_INVALID)
            as $csvColumn => $entry
        ) {
            $symfonyStyle->error('Invalid csv entry ' . $entry . 'in line: ' . $csvColumn);
        }

        foreach (
            $this->importGlossaryEntryService->getFailedEntries(ImportGlossaryEntryService::ERROR_EXISTING)
            as $column => $entry
        ) {
            $symfonyStyle->info('Skipping entry ' . $entry . ' as it already exists. Line: ' . $column);
        }

        foreach (
            $this->importGlossaryEntryService->getFailedEntries(ImportGlossaryEntryService::ERROR_LOCALIZATION)
            as $entry
        ) {
            $symfonyStyle->error('Failed to localize entry: ' . $entry);
        }

        $symfonyStyle->info('Imported ' . $successCount . '/' . $allEntriesCount . ' entries.');
    }

    protected function translationExistsForPage(int $pageId, int $sysLanguageId): bool
    {
        $translations = GeneralUtility::makeInstance(TranslationConfigurationProvider::class)
            ->translationInfo('pages', $pageId);

        if (is_array($translations) === false) {
            return false;
        }

        foreach ($translations['translations'] as $translation) {
            if ((int)($translation['sys_language_uid'] ?? 0) === $sysLanguageId) {
                return true;
            }
        }

        return false;
    }
}
