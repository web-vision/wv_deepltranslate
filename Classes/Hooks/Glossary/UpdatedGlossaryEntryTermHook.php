<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Hooks\Glossary;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\Deepltranslate\Core\Domain\Repository\GlossaryEntryRepository;
use WebVision\Deepltranslate\Core\Domain\Repository\GlossaryRepository;

class UpdatedGlossaryEntryTermHook
{
    private GlossaryRepository $glossaryRepository;

    private GlossaryEntryRepository $glossaryEntryRepository;

    public function __construct(
        GlossaryRepository $glossaryRepository,
        GlossaryEntryRepository $glossaryEntryRepository
    ) {
        $this->glossaryRepository = $glossaryRepository;
        $this->glossaryEntryRepository = $glossaryEntryRepository;
    }

    /**
     * @param int|string $id
     * @param array{glossary: int} $fieldArray
     *
     * @throws DBALException
     * @throws Exception
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        if ($status !== 'update') {
            return;
        }

        if ($table !== 'tx_wvdeepltranslate_glossaryentry') {
            return;
        }

        $glossary = $this->glossaryEntryRepository->findEntryByUid($id);

        if (empty($glossary)) {
            return;
        }

        $this->glossaryRepository->setGlossaryNotSyncOnPage($glossary['pid']);

        $flashMessage = new FlashMessage(
            (string)LocalizationUtility::translate(
                'glossary.not-sync.message',
                'DeepltranslateCore'
            ),
            (string)LocalizationUtility::translate(
                'glossary.not-sync.title',
                'DeepltranslateCore'
            ),
            ContextualFeedbackSeverity::INFO,
            true
        );

        GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier()
            ->enqueue($flashMessage);
    }
}
