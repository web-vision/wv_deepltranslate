<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;
use WebVision\WvDeepltranslate\Traits\GlossarySyncTrait;

class GlossarySyncCommand extends Command
{
    use GlossarySyncTrait;
    protected DeeplGlossaryService $deeplGlossaryService;

    protected GlossaryRepository $glossaryRepository;
    public function __construct(
        string $name = null,
        ?DeeplGlossaryService $deeplGlossaryService = null,
        ?GlossaryRepository $glossaryRepository = null
    ) {
        parent::__construct($name);
        $this->deeplGlossaryService = $deeplGlossaryService ?? GeneralUtility::makeInstance(DeeplGlossaryService::class);
        $this->glossaryRepository = $glossaryRepository ?? GeneralUtility::makeInstance(GlossaryRepository::class);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setDescription('Sync all glossaries to DeepL API')
            ->addOption(
            'pageId',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Page to sync, not set, sync all glossaries',
            0
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pageId = $input->getOption('pageId');
        $glossaries = $this->glossaryRepository->findAllGlossaries($pageId);

        foreach ($glossaries as $glossary) {
            $this->syncSingleGlossary($glossary['uid']);
        }

        /**
         * return 0 HAS to be for TYPO3 v9 support
         * @see https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html#return-value
         */
        return 0;
    }
}
