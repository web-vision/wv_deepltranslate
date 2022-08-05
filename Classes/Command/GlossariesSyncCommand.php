<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Command;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2022 Kallol Chakraborty <kallol@web-vision.de>, web-vision GmbH
 *
 *  You may not remove or change the name of the author above. See:
 *  http://www.gnu.org/licenses/gpl-faq.html#IWantCredit
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebVision\WvDeepltranslate\Domain\Model\Glossaries;
use WebVision\WvDeepltranslate\Domain\Model\Glossariessync;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariessyncRepository;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

/**
 * Class GlossariesSyncCommand
 */
class GlossariesSyncCommand extends Command
{
    protected DeeplGlossaryService $deeplGlossaryService;

    protected GlossariesRepository $glossariesRepository;

    protected GlossariessyncRepository $glossariessyncRepository;

    protected LanguageRepository $languageRepository;

    protected PersistenceManager $persistenceManager;

    public function configure(): void
    {
        $this->setDescription('Cleanup Glossary entries in DeepL Database');
    }

    /**
     * Executes the command
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // Instantiate objects
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplGlossaryService = $objectManager->get(DeeplGlossaryService::class);
        $this->glossariesRepository = $objectManager->get(GlossariesRepository::class);
        $this->glossariessyncRepository = $objectManager->get(GlossariessyncRepository::class);

        $this->doCleanupTasks($output);

        return Command::SUCCESS;
    }

    /**
     * @return void
     */
    private function doCleanupTasks(OutputInterface $output)
    {
        // Step - 1: Delete glossaries from DeepL
        $glossaries = $this->deeplGlossaryService->listGlossaries();

        $output->writeln([
            'List of Glossary entries',
            '============',
            '',
        ]);

        foreach($glossaries['glossaries'] as $eachGlossary) {
            $output->writeln($eachGlossary);
            $glId = $eachGlossary['glossary_id'];
            $this->deeplGlossaryService->deleteGlossary($glId);
        }

        // Step - 2: Delete DB records related to sync
        $this->glossariessyncRepository->truncateDbSyncRecords();
    }
}
