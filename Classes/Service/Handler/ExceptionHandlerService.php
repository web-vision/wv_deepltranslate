<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Service\Handler;

use Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ExceptionHandlerService
{
    protected FlashMessageQueue $messageQueue;

    public function __construct(
        ?FlashMessageService $flashMessageService = null
    ) {
        $this->messageQueue =
            ($flashMessageService ?? GeneralUtility::makeInstance(FlashMessageService::class))
                ->getMessageQueueByIdentifier();
    }

    /**
     * @param array<int, array{
     *     exception: Exception,
     *      item: mixed
     * }>|array{
     *     exception: Exception,
     *      item: mixed
     * } ...$exceptions
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function generateFlashMessages(...$exceptions): void
    {
        foreach ($exceptions as $exception) {
            $this->messageQueue->enqueue(
                $this->generateFlashMessage(
                    $exception['exception'],
                    $exception['item']
                )
            );
        }
    }

    /**
     * @param mixed $item
     */
    public function generateFlashMessage(Exception $exception, $item): FlashMessage
    {
        return GeneralUtility::makeInstance(
            FlashMessage::class,
            LocalizationUtility::translate(
                'glossary.sync.exception' . (new \ReflectionClass($exception))->getShortName(),
                'wv_deepltranslate',
                [
                    0 => $item['title'] ?? '',
                ]
            ),
            LocalizationUtility::translate(
                'glossary.sync.error.title',
                'wv_deepltranslate'
            )
        );
    }
}
