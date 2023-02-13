<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesSyncRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;

class GlossariesEntriesCleanupCommand extends Command
{
    protected DeeplService $deeplService;

    protected GlossariesRepository $glossariesRepository;

    protected GlossariesSyncRepository $glossariesSyncRepository;

    protected PersistenceManager $persistenceManager;

    public function configure(): void
    {
        $this->setDescription('Cleanup Glossary entries in DeepL Database');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // Instantiate objects
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplService = $objectManager->get(DeeplService::class);
        $this->glossariesRepository = $objectManager->get(GlossariesRepository::class);
        $this->glossariesSyncRepository = $objectManager->get(GlossariesSyncRepository::class);

        $this->removeAllGloassaryEntries($output);

        return Command::SUCCESS;
    }

    private function removeAllGloassaryEntries(OutputInterface $output): void
    {
        $glossaries = $this->deeplService->listGlossaries();

        $output->writeln([
            'List of Glossary entries',
            '============',
            '',
        ]);

        if (! empty($glossaries)) {
            foreach ($glossaries['glossaries'] as $eachGlossary) {
                $output->writeln($eachGlossary);
                $id = $eachGlossary['glossary_id'];
                $this->deeplService->deleteGlossary($id);
            }

            $this->glossariesSyncRepository->truncateDbSyncRecords();
        }
    }
}
