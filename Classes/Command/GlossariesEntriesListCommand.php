<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Model\Glossaries;
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
        $this->setDescription('List Glossary entries by Id');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // Instantiate objects
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplGlossaryService = $objectManager->get(DeeplGlossaryService::class);
        $this->glossariesRepository = $objectManager->get(GlossariesRepository::class);
        $this->glossariessyncRepository = $objectManager->get(GlossariessyncRepository::class);

        $this->listAllGloassaryEntriesById($output, 'af483be5-5428-4f97-bbfc-5069ab07cfe1');

        return Command::SUCCESS;
    }

    private function listAllGloassaryEntries(OutputInterface $output): void
    {
        $glossaries = $this->deeplGlossaryService->listGlossaries();

        $output->writeln([
            'List of Glossary entries',
            '============',
            '',
        ]);

        foreach ($glossaries['glossaries'] as $eachGlossary) {
            $output->writeln($eachGlossary);
            $id = $eachGlossary['glossary_id'];
        }
    }

    private function listAllGloassaryEntriesById(OutputInterface $output, $id): void
    {
        $glossaries = $this->deeplGlossaryService->glossaryEntries($id);

        $output->writeln([
            'List of Glossary entries',
            '============',
            '',
        ]);

        $output->writeln($glossaries);
    }
}
