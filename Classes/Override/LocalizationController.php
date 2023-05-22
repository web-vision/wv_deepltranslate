<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Override;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use WebVision\WvDeepltranslate\Configuration;
use WebVision\WvDeepltranslate\Service\DeeplService;

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

    private const ACTION_LOCALIZEGOOGLE = 'localizegoogle';

    private const ACTION_LOCALIZEGOOGLE_AUTO = 'localizegoogleauto';

    protected DeeplService $deeplService;

    protected PageRenderer $pageRenderer;
    private Configuration $configuration;

    public function __construct()
    {
        parent::__construct();

        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->configuration = GeneralUtility::makeInstance(Configuration::class);
        $this->deeplService = GeneralUtility::makeInstance(DeeplService::class);
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf');
    }

    /**
     * Get used languages in a page
     */
    public function getUsedLanguagesInPage(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (!isset($params['pageId'], $params['languageId'])) {
            return new JsonResponse(null, 400);
        }

        $pageId     = (int)$params['pageId'];
        $languageId = (int)$params['languageId'];
        $mode       = $params['mode'] ?? '';

        /** @var TranslationConfigurationProvider $translationProvider */
        $translationProvider = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
        $systemLanguages     = $translationProvider->getSystemLanguages($pageId);

        $availableLanguages = [];

        // First check whether column has localized records
        $elementsInColumnCount = $this->localizationRepository->getLocalizedRecordCount($pageId, $languageId);
        $result = [];
        if ($elementsInColumnCount !== 0) {
            // check elements in column - empty if source records do not exist anymore
            $result = $this->localizationRepository->fetchOriginLanguage($pageId, $languageId);
            if ($result !== []) {
                $availableLanguages[] = $systemLanguages[$result['sys_language_uid']];
            }
        }
        if ($elementsInColumnCount === 0 || $result === []) {
            $fetchedAvailableLanguages = $this->localizationRepository->fetchAvailableLanguages($pageId, $languageId);
            foreach ($fetchedAvailableLanguages as $language) {
                if (isset($systemLanguages[$language['sys_language_uid']])) {
                    $availableLanguages[] = $systemLanguages[$language['sys_language_uid']];
                }
            }
        }
        // Language "All" should not appear as a source of translations (see bug 92757) and keys should be sequential
        $availableLanguages = array_values(
            array_filter($availableLanguages, static function (array $languageRecord): bool {
                return (int)$languageRecord['uid'] !== -1;
            })
        );

        //for deepl and google auto modes
        if (!empty($availableLanguages)) {
            if ($mode == 'localizedeeplauto' || $mode == 'localizegoogleauto') {
                foreach ($availableLanguages as &$availableLanguage) {
                    $availableLanguage['uid']     = 'auto-' . $availableLanguage['uid'];
                    $availableLanguage['ISOcode'] = 'AUT';
                }
            }
        }

        // Pre-render all flag icons
        foreach ($availableLanguages as &$language) {
            if ($language['flagIcon'] === 'empty-empty') {
                $language['flagIcon'] = '';
            } else {
                $language['flagIcon'] = $this->iconFactory->getIcon($language['flagIcon'], Icon::SIZE_SMALL)->render();
            }
        }

        return (new JsonResponse())->setPayload($availableLanguages);
    }

    /**
     * Get a prepared summary of records being translated
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getRecordLocalizeSummary(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (!isset($params['pageId'], $params['destLanguageId'], $params['languageId'])) {
            return new JsonResponse(null, 400);
        }

        $pageId         = (int)$params['pageId'];
        $destLanguageId = (int)$params['destLanguageId'];
        //getting source language id
        $languageId = $this->getSourceLanguageid($params['languageId']);

        $records = [];
        $result  = $this->localizationRepository->getRecordsToCopyDatabaseResult(
            $pageId,
            $destLanguageId,
            $languageId,
            '*'
        );

        while ($row = $result->fetch()) {
            BackendUtility::workspaceOL('tt_content', $row, -99, true);
            if (!$row || VersionState::cast($row['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
                continue;
            }
            $colPos = $row['colPos'];
            if (!isset($records[$colPos])) {
                $records[$colPos] = [];
            }
            $records[$colPos][] = [
                'icon'  => $this->iconFactory->getIconForRecord('tt_content', $row, Icon::SIZE_SMALL)->render(),
                'title' => $row[$GLOBALS['TCA']['tt_content']['ctrl']['label']],
                'uid'   => $row['uid'],
            ];
        }

        $payloadBody = [
            'records' => $records,
            'columns' => $this->getPageColumns($pageId, $records, $params),
        ];

        // s. EXT:containers Xclass B13\Container\Xclasses\LocalizationController
        if (
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('container')
            && class_exists(\B13\Container\Xclasses\RecordLocalizeSummaryModifier::class)
        ) {
            $recordLocalizeSummaryModifier = GeneralUtility::makeInstance(\B13\Container\Xclasses\RecordLocalizeSummaryModifier::class);
            $payloadBody = $recordLocalizeSummaryModifier->rebuildPayload($payloadBody);
        }

        return (new JsonResponse())->setPayload($payloadBody);
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
        if ($params['action'] !== static::ACTION_COPY && $params['action'] !== static::ACTION_LOCALIZE && $params['action'] !== static::ACTION_LOCALIZEDEEPL && $params['action'] !== static::ACTION_LOCALIZEDEEPL_AUTO && $params['action'] !== static::ACTION_LOCALIZEGOOGLE && $params['action'] !== static::ACTION_LOCALIZEGOOGLE_AUTO) {
            $response = new Response('php://temp', 400, ['Content-Type' => 'application/json; charset=utf-8']);
            $response->getBody()->write('Invalid action "' . $params['action'] . '" called.');
            return $response;
        }

        // Filter transmitted but invalid uids
        $params['uidList'] = $this->filterInvalidUids(
            (int)$params['pageId'],
            (int)$params['destLanguageId'],
            $this->getSourceLanguageid($params['srcLanguageId']),
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
        $destLanguageId = (int)$params['destLanguageId'];

        // Build command map
        $cmd = [
            'tt_content' => [],
        ];

        if (isset($params['uidList']) && is_array($params['uidList'])) {
            foreach ($params['uidList'] as $currentUid) {
                if ($params['action'] === static::ACTION_LOCALIZE || $params['action'] === static::ACTION_LOCALIZEDEEPL || $params['action'] === static::ACTION_LOCALIZEDEEPL_AUTO || $params['action'] === static::ACTION_LOCALIZEGOOGLE || $params['action'] === static::ACTION_LOCALIZEGOOGLE_AUTO) {
                    $cmd['tt_content'][$currentUid] = [
                        'localize' => $destLanguageId,
                    ];
                    //setting mode and source language for deepl translate.
                    if ($params['action'] === static::ACTION_LOCALIZEDEEPL || $params['action'] === static::ACTION_LOCALIZEDEEPL_AUTO) {
                        $cmd['localization']['custom']['mode']          = 'deepl';
                        $cmd['localization']['custom']['srcLanguageId'] = $params['srcLanguageId'];
                    } elseif ($params['action'] === static::ACTION_LOCALIZEGOOGLE || $params['action'] === static::ACTION_LOCALIZEGOOGLE_AUTO) {
                        $cmd['localization']['custom']['mode']          = 'google';
                        $cmd['localization']['custom']['srcLanguageId'] = $params['srcLanguageId'];
                    }
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
     * check deepl Settings (url,apikey).
     * @param ServerRequestInterface $request
     * @return array
     */
    public function checkdeeplSettings(ServerRequestInterface $request)
    {
        $result = [];
        if ($this->configuration->getApiKey() != null && $this->configuration->getApiUrl() != null) {
            $result['status'] = 'true';
        } else {
            $result['status']  = 'false';
            $result['message'] = 'Deepl settings not enabled';
        }

        $result = json_encode($result);
        echo $result;
        exit;
    }

    /**
     * Return source language Id from source language string
     * @param string $srcLanguage
     * @return int
     */
    public function getSourceLanguageid($srcLanguage)
    {
        $langParam = explode('-', $srcLanguage);
        if (count($langParam) > 1) {
            return (int)$langParam[1];
        }
        return (int)$langParam[0];
    }
}
