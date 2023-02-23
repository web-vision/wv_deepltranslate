<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Controller;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Exception\InvalidArgumentException;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;
use WebVision\WvDeepltranslate\Traits\GlossarySyncTrait;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

class GlossarySyncController
{
    use GlossarySyncTrait;
    protected DeeplGlossaryService $deeplGlossaryService;

    protected GlossaryRepository $glossaryRepository;

    public function __construct(
        ?DeeplGlossaryService $deeplGlossaryService = null,
        ?GlossaryRepository $glossaryRepository = null
    ) {
        $this->deeplGlossaryService = $deeplGlossaryService ?? GeneralUtility::makeInstance(DeeplGlossaryService::class);
        $this->glossaryRepository = $glossaryRepository ?? GeneralUtility::makeInstance(GlossaryRepository::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function update(ServerRequestInterface $request)
    {
        $processingParameters = $request->getQueryParams();

        if (!isset($processingParameters['mode'])) {
            throw new InvalidArgumentException(
                'Mode is not defined. Synchronization not completed.',
                1676935386416
            );
        }

        if (
            $processingParameters['mode'] !== DeeplBackendUtility::RENDER_TYPE_ELEMENT
            && $processingParameters['mode'] !== DeeplBackendUtility::RENDER_TYPE_PAGE
        ) {
            throw new InvalidArgumentException(
                'No mode' . $processingParameters['mode'] . ' defined',
                1676935573680
            );
        }

        if (!isset($processingParameters['uid'])) {
            throw new InvalidArgumentException(
                'No ID given for glossary synchronization',
                1676935668643
            );
        }

        switch ($processingParameters['mode']) {
            case DeeplBackendUtility::RENDER_TYPE_PAGE:
                $this->syncGlossariesOfPage((int)$processingParameters['uid']);
                break;
            case DeeplBackendUtility::RENDER_TYPE_ELEMENT:
                $this->syncSingleGlossary((int)$processingParameters['uid']);
                break;
        }

        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            LocalizationUtility::translate(
                'glossary.sync.message',
                'wv_deepltranslate'
            ),
            LocalizationUtility::translate(
                'glossary.sync.title',
                'wv_deepltranslate'
            )
        );
        GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier()
            ->enqueue($flashMessage);

        return new RedirectResponse($processingParameters['returnUrl']);
    }

    private function syncGlossariesOfPage(int $uid): void
    {
        $glossaries = $this->glossaryRepository->findAllGlossaries($uid);

        foreach ($glossaries as $glossary) {
            $this->syncSingleGlossary($glossary['uid']);
        }
    }
}
