<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class GlossarySyncCommand extends Command
{
    use GlossaryCommandTrait;

    protected function configure(): void
    {
        $this
            ->addOption(
                'pageId',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Page to sync, not set, sync all glossaries',
                0
            );
    }

    /**
     * @throws SiteNotFoundException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $pageId = (int)$input->getOption('pageId');
        $glossaries = [$pageId];
        if ($pageId === 0) {
            $glossaries = $this->glossaryRepository->findAllGlossaries();
        }

        foreach ($glossaries as $glossary) {
            $this->deeplGlossaryService->syncGlossaries($glossary['uid']);
        }

        return Command::SUCCESS;
    }
}
