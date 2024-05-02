<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Domain\Repository\PageRepository;
use WebVision\WvDeepltranslate\Exception\ApiKeyNotSetException;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\LanguageService;

abstract class AbstractTranslateHook
{
    /**
     * @var array{tableName: string|null, id: string|int|null, mode: string|false}
     */
    protected static array $coreProcessorsInformation = [
        'tableName' => null,
        'id' => null,
        // @todo rename identifier to "deepl"
        'mode' => false,
    ];

    protected DeeplService $deeplService;

    protected PageRepository $pageRepository;

    protected LanguageService $languageService;

    public function __construct(
        PageRepository $pageRepository,
        DeeplService $deeplService,
        LanguageService $languageService
    ) {
        $this->deeplService = $deeplService;
        $this->pageRepository = $pageRepository;
        $this->languageService = $languageService;
    }

    /**
     * These logics were outsourced to test them and later to resolve them in a service
     */
    public function translateContent(
        string $content,
        string $sourceLanguageIsocode,
        string $targetLanguageIsocode
    ): string {
        try {
            $response = $this->deeplService->translateRequest(
                $content,
                $targetLanguageIsocode,
                $sourceLanguageIsocode
            );
        } catch (ApiKeyNotSetException $exception) {
            // @todo Add proper error logging here.
            return $content;
        }

        if ($response === null) {
            if (!Environment::isCli() || !Environment::getContext()->isTesting()) {
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    'Translation not successful', // ToDo use locallang label
                    '',
                    -1
                );
                GeneralUtility::makeInstance(FlashMessageService::class)
                    ->getMessageQueueByIdentifier()
                    ->addMessage($flashMessage);
            }

            return $content;
        }

        if (is_array($response)) {
            $content = '';
            foreach ($response as $result) {
                $content .= $result->text;
            }
        } else {
            $content = $response->text;
        }

        return htmlspecialchars_decode($content, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    }

    /**
     * @param string $id
     * @param mixed $value
     * @param int $pasteUpdate
     */
    public function processCmdmap(
        string $command,
        string $table,
        $id,
        $value,
        bool $commandIsProcessed,
        DataHandler $dataHandler,
        $pasteUpdate
    ): void {
        if ($commandIsProcessed !== false) {
            return;
        }

        self::$coreProcessorsInformation['tableName'] = $table;
        self::$coreProcessorsInformation['id'] = $id;
        self::$coreProcessorsInformation['mode'] = $dataHandler->cmdmap['localization']['custom']['mode'] ?? false;
    }
}
