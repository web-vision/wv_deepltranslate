<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Override;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\Deepltranslate\Core\Service\DeeplService;

/**
 * LocalizationController handles the AJAX requests for record localization
 *
 * @internal
 * @override
 */
class LocalizationController extends \TYPO3\CMS\Backend\Controller\Page\LocalizationController
{
    private const ACTION_LOCALIZEDEEPL = 'localizedeepl';

    private const ACTION_LOCALIZEDEEPL_AUTO = 'localizedeeplauto';

    protected DeeplService $deeplService;

    protected PageRenderer $pageRenderer;

    public function __construct()
    {
        parent::__construct();

        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->deeplService = GeneralUtility::makeInstance(DeeplService::class);
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf');
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function localizeRecords(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        if (!isset($params['pageId'], $params['srcLanguageId'], $params['destLanguageId'], $params['action'], $params['uidList'])) {
            return new JsonResponse(null, 400);
        }

        //additional constraint ACTION_LOCALIZEDEEPL
        if (
            $params['action'] !== static::ACTION_COPY
            && $params['action'] !== static::ACTION_LOCALIZE
            && $params['action'] !== static::ACTION_LOCALIZEDEEPL
            && $params['action'] !== static::ACTION_LOCALIZEDEEPL_AUTO
        ) {
            $response = new Response('php://temp', 400, ['Content-Type' => 'application/json; charset=utf-8']);
            $response->getBody()->write('Invalid action "' . $params['action'] . '" called.');
            return $response;
        }

        // Filter transmitted but invalid uids
        $params['uidList'] = $this->filterInvalidUids(
            (int)$params['pageId'],
            (int)$params['destLanguageId'],
            $this->getSourceLanguageId($params['srcLanguageId']),
            $params['uidList']
        );

        $this->process($params);

        return (new JsonResponse())->setPayload([]);
    }

    /**
     * Processes the localization actions
     *
     * @param array $params
     */
    protected function process($params): void
    {
        $deeplTranslateActions = [static::ACTION_LOCALIZEDEEPL, static::ACTION_LOCALIZEDEEPL_AUTO];
        $destLanguageId = (int)$params['destLanguageId'];

        // Build command map
        $cmd = [
            'tt_content' => [],
        ];
        if (isset($params['uidList']) && is_array($params['uidList'])) {
            foreach ($params['uidList'] as $currentUid) {
                if (
                    $params['action'] === static::ACTION_LOCALIZE
                    || $params['action'] === static::ACTION_LOCALIZEDEEPL
                    || $params['action'] === static::ACTION_LOCALIZEDEEPL_AUTO
                ) {
                    $dataHandlerCommandName = (in_array($params['action'], $deeplTranslateActions, true) ? 'deepltranslate' : 'localize');
                    $cmd['tt_content'][$currentUid] = [
                        $dataHandlerCommandName => $destLanguageId,
                    ];
                } else {
                    $cmd['tt_content'][$currentUid] = [
                        'copyToLanguage' => $destLanguageId,
                    ];
                }
            }
        }

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], $cmd);
        $dataHandler->process_cmdmap();
    }

    /**
     * Return source language ID from source language string
     */
    public function getSourceLanguageId(string $srcLanguage): int
    {
        $langParam = explode('-', $srcLanguage);
        if (count($langParam) > 1) {
            return (int)$langParam[1];
        }
        return (int)$langParam[0];
    }
}
