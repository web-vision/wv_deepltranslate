<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Controller;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\Deepltranslate\Core\Exception\FailedToCreateGlossaryException;
use WebVision\Deepltranslate\Core\Exception\InvalidArgumentException;
use WebVision\Deepltranslate\Core\Service\DeeplGlossaryService;

class GlossarySyncController
{
    protected DeeplGlossaryService $deeplGlossaryService;

    private FlashMessageService $flashMessageService;

    public function __construct(
        DeeplGlossaryService $deeplGlossaryService,
        FlashMessageService $flashMessageService
    ) {
        $this->deeplGlossaryService = $deeplGlossaryService;
        $this->flashMessageService = $flashMessageService;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(ServerRequestInterface $request): RedirectResponse
    {
        $processingParameters = $request->getQueryParams();

        if (!isset($processingParameters['uid'])) {
            $this->flashMessageService
                ->getMessageQueueByIdentifier()
                ->enqueue((new FlashMessage(
                    'No ID given for glossary synchronization',
                    '',
                    2,
                    true
                )));
            return new RedirectResponse($processingParameters['returnUrl']);
        }

        // Check page configuration of glossary type
        /** @var array{uid: int, doktype: string|int, module: string} $pages */
        $pages = BackendUtility::getRecord('pages', (int)$processingParameters['uid']);
        if ((int)$pages['doktype'] !== PageRepository::DOKTYPE_SYSFOLDER && $pages['module'] !== 'glossary') {
            $this->flashMessageService->getMessageQueueByIdentifier()->enqueue(new FlashMessage(
                sprintf('Page "%d" not configured for glossary synchronization.', $pages['uid']),
                (string)LocalizationUtility::translate(
                    'glossary.sync.title.invalid',
                    'DeepltranslateCore'
                ),
                2,
                true
            ));
            return new RedirectResponse($processingParameters['returnUrl']);
        }

        try {
            $this->deeplGlossaryService->syncGlossaries((int)$processingParameters['uid']);
            $this->flashMessageService->getMessageQueueByIdentifier()->enqueue(new FlashMessage(
                (string)LocalizationUtility::translate('glossary.sync.message', 'DeepltranslateCore'),
                (string)LocalizationUtility::translate('glossary.sync.title', 'DeepltranslateCore'),
                0, // OK
                true
            ));
        } catch (FailedToCreateGlossaryException $exception) {
            $this->flashMessageService->getMessageQueueByIdentifier()->enqueue(new FlashMessage(
                (string)LocalizationUtility::translate('glossary.sync.message.invalid', 'DeepltranslateCore'),
                (string)LocalizationUtility::translate('glossary.sync.title.invalid', 'DeepltranslateCore'),
                2, // Error
                true
            ));
        }

        return new RedirectResponse($processingParameters['returnUrl']);
    }
}
