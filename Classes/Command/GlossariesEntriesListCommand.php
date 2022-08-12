<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariessyncRepository;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class GlossariesEntriesListCommand extends Command
{
    protected DeeplGlossaryService $deeplGlossaryService;

    protected GlossariesRepository $glossariesRepository;

    protected GlossariessyncRepository $glossariessyncRepository;

    protected LanguageRepository $languageRepository;

    protected PersistenceManager $persistenceManager;

    public function configure(): void
    {
        $this->setDescription('List Glossary entries or entries by glossary_id');
        $this->addArgument('glossary_id', InputArgument::OPTIONAL, 'Which glossary you want to fetch (id)?');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // Instantiate objects
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplGlossaryService = $objectManager->get(DeeplGlossaryService::class);
        $this->glossariesRepository = $objectManager->get(GlossariesRepository::class);
        $this->glossariessyncRepository = $objectManager->get(GlossariessyncRepository::class);

        $glossary_id = $input->getArgument('glossary_id');

        if ($glossary_id) {
            $this->listAllGloassaryEntriesById($output, $glossary_id);
            return Command::SUCCESS;
        }

        $this->listAllGloassaryEntries($output);
        return Command::SUCCESS;
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
            'target_lang - ' . $information['target_lang']
        ];

        $rows = array_map(null, array_keys($entries), $entries);

        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }
}
