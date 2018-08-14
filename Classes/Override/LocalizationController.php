<?php
namespace PITS\Deepl\Override;

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
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Core\Page\PageRenderer;
/**
 * LocalizationController handles the AJAX requests for record localization
 */
class LocalizationController extends \TYPO3\CMS\Backend\Controller\Page\LocalizationController
{

    /**
     * @const string
     */
    const ACTION_LOCALIZEDEEPL = 'localizedeepl';
    
    /**
     * @const string
     */
    
    const ACTION_LOCALIZEDEEPL_AUTO = 'localizedeeplauto';
    
    /**
     * @const string
     */
    
    const ACTION_LOCALIZEGOOGLE = 'localizegoogle';
    
    /**
     * @const string
     */
    const ACTION_LOCALIZEGOOGLE_AUTO = 'localizegoogleauto';
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */

    /**
     * @var type
     */
    protected $deeplService;

    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     */
    protected $pageRenderer;
    
     /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:deepltranslate/Resources/Private/Language/locallang.xlf');
    }

    /**
     * localizeRecords
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return type
     */
    public function localizeRecords(ServerRequestInterface $request, ResponseInterface $response)
    {
       
        $params = $request->getQueryParams();

        if (!isset($params['pageId'], $params['srcLanguageId'], $params['destLanguageId'], $params['action'], $params['uidList'])) {
            $response = $response->withStatus(400);
            return $response;
        }
        //additional constraint ACTION_LOCALIZEDEEPL
        if ($params['action'] !== static::ACTION_COPY && $params['action'] !== static::ACTION_LOCALIZE && $params['action'] !== static::ACTION_LOCALIZEDEEPL && $params['action'] !== static::ACTION_LOCALIZEDEEPL_AUTO && $params['action'] !== static::ACTION_LOCALIZEGOOGLE && $params['action'] !== static::ACTION_LOCALIZEGOOGLE_AUTO) {
            $response->getBody()->write('Invalid action "' . $params['action'] . '" called.');
            $response = $response->withStatus(400);
            return $response;
        }

        $this->process($params);

        $response->getBody()->write(json_encode([]));
        return $response;
    }

    /**
     * Processes the localization actions
     *
     * @param array $params
     */
    protected function process($params)
    {
        $destLanguageId = (int) $params['destLanguageId'];
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
                    }
                    else if($params['action'] === static::ACTION_LOCALIZEGOOGLE || $params['action'] === static::ACTION_LOCALIZEGOOGLE_AUTO){
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
     * Get used languages in a colPos of a page
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function getUsedLanguagesInPageAndColumn(ServerRequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getQueryParams();
        if (!isset($params['pageId'], $params['colPos'], $params['languageId'])) {
            $response = $response->withStatus(400);
            return $response;
        }
        $pageId     = (int) $params['pageId'];
        $colPos     = (int) $params['colPos'];
        $languageId = (int) $params['languageId'];
        $mode       = $params['mode'];
        /** @var TranslationConfigurationProvider $translationProvider */
        $translationProvider = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
        $systemLanguages     = $translationProvider->getSystemLanguages($pageId);

        $availableLanguages = [];
        
        // First check whether column has localized records
        $elementsInColumnCount = $this->localizationRepository->getLocalizedRecordCount($pageId, $colPos, $languageId);
        
        if ($elementsInColumnCount === 0) {
            $fetchedAvailableLanguages = $this->localizationRepository->fetchAvailableLanguages($pageId, $colPos, $languageId);
            $availableLanguages[]      = $systemLanguages[0];
            
            foreach ($fetchedAvailableLanguages as $language) {
                if (isset($systemLanguages[$language['uid']])) {
                    $availableLanguages[] = $systemLanguages[$language['uid']];
                }
            }
        } else {
            $result = $this->localizationRepository->fetchOriginLanguage($pageId, $colPos, $languageId);
            $availableLanguages[] = $systemLanguages[$result['sys_language_uid']];
        }
        
        //$availableLanguages      = array_filter($availableLanguages);
        if (!empty($availableLanguages)) {
            if ($mode == 'localizedeeplauto' || $mode == 'localizegoogleauto') {
                foreach ($availableLanguages as &$availableLanguage) {
                    $availableLanguage['uid'] = 'auto-' . $availableLanguage['uid'];
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

        $response->getBody()->write(json_encode($availableLanguages));
        return $response;
    }

    /**
     * Get a prepared summary of records being translated
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function getRecordLocalizeSummary(ServerRequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getQueryParams();
        if (!isset($params['pageId'], $params['colPos'], $params['destLanguageId'], $params['languageId'])) {
            $response = $response->withStatus(400);
            return $response;
        }

        $records = [];
        //getting source language id
        $langParam = explode('-', $params['languageId']);
        if (count($langParam) > 1) {
            $params['languageId'] = $langParam[1];
        } else {
            $params['languageId'] = $langParam[0];
        }
        //get content element
        $result = $this->localizationRepository->getRecordsToCopyDatabaseResult(
            $params['pageId'],
            $params['colPos'],
            $params['destLanguageId'],
            $params['languageId'],
            '*'
        );
        while ($row = $result->fetch()) {
            BackendUtility::workspaceOL('tt_content', $row, -99, true);
            if (!$row || VersionState::cast($row['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
                continue;
            }
            $records[] = [
                'icon'  => $this->iconFactory->getIconForRecord('tt_content', $row, Icon::SIZE_SMALL)->render(),
                'title' => $row[$GLOBALS['TCA']['tt_content']['ctrl']['label']],
                'uid'   => $row['uid'],
            ];
        }

        $response->getBody()->write(json_encode($records));
        return $response;
    }

    /**
     * check deepl Settings (url,apikey).
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return array
     */
    public function checkdeeplSettings(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->deeplService    = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('PITS\\Deepl\\Service\\DeeplService');
        $result                = [];
        $extConf               = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deepltranslate']);
        if ($this->deeplService->apiKey != null && $this->deeplService->apiUrl != null) {
            $result['status'] = 'true';
        } else {
            $result['status']  = 'false';
            $result['message'] = 'Deepl settings not enabled';
        }
        $result = json_encode($result);
        echo $result;
        exit;
    }
}
