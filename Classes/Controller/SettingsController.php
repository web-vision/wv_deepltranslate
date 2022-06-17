<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Ricky Mathew <ricky@web-vision.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;

/**
 * Class SettingsController
 */
class SettingsController extends ActionController
{
    protected PageRenderer $pageRenderer;

    protected SettingsRepository $settingsRepository;

    protected DeeplService $deeplService;

    public function injectPageRenderer(PageRenderer $pageRenderer)
    {
        $this->pageRenderer = $pageRenderer;
    }

    public function injectDeeplSettingsRepository(SettingsRepository $deeplSettingsRepository)
    {
        $this->settingsRepository = $deeplSettingsRepository;
    }

    public function injectDeeplService(DeeplService $deeplService)
    {
        $this->deeplService = $deeplService;
    }

    public function indexAction(): void
    {
        $args = $this->request->getArguments();
        if (!empty($args) && $args['redirectFrom'] == 'savesetting') {
            $successMessage = LocalizationUtility::translate('settings_success', 'Deepl');
            $this->pageRenderer->addJsInlineCode(
                'success',
                "top.TYPO3.Notification.success('Saved', '" . $successMessage . "');"
            );
        }

        $sysLanguages = $this->settingsRepository->getSysLanguages();
        if (empty($sysLanguages)) {
            $this->addFlashMessage(
                'No system languages found.',
                'Errors system language',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }

        $preSelect = [];

        //get existing assignments if any
        $languageAssignments = $this->settingsRepository->getAssignments();
        if (!empty($languageAssignments) && !empty($languageAssignments['languages_assigned'])) {
            $preSelect = array_filter(unserialize($languageAssignments['languages_assigned']));
        }

        $selectBox = $this->buildTableAssignments($sysLanguages, $preSelect);
        $this->view->assignMultiple([
            'sysLanguages' => $sysLanguages,
            'selectBox' => $selectBox
        ]);
    }

    public function saveSettingsAction(): void
    {
        $args = $this->request->getArguments();
        if (!empty($args['languages'])) {
            $languages = array_filter($args['languages']);
        }

        $data = [];
        if (!empty($languages)) {
            $data['languages_assigned'] = serialize($languages);
        }

        //get existing assignments if any
        $languageAssignments = $this->settingsRepository->getAssignments();
        if (empty($languageAssignments)) {
            $data['crdate'] = time();
            $this->settingsRepository->insertDeeplSettings($data);
        } else {
            $data['uid'] = $languageAssignments[0]['uid'];
            $this->settingsRepository->updateDeeplSettings($data);
        }

        $args['redirectFrom'] = 'savesetting';
        $this->redirect('index', 'Settings', 'Deepl', $args);
    }

    /**
     * return an array of options for multiple selectbox
     *
     * @param array $sysLanguages
     * @param array $preselectedValues
     *
     * @return array[]
     */
    public function buildTableAssignments(array $sysLanguages, array $preselectedValues): array
    {
        $table = [];
        $selectedKeys = array_keys($preselectedValues);
        foreach ($sysLanguages as $sysLanguage) {
            $syslangIso = $sysLanguage['language_isocode'];
            $option = [];
            $option = $sysLanguage;
            if (in_array($sysLanguage['uid'], $selectedKeys) || in_array(
                strtoupper($sysLanguage['language_isocode']),
                $this->deeplService->apiSupportedLanguages
            )) {
                $option['value'] = $preselectedValues[$sysLanguage['uid']] ?? strtoupper($syslangIso);
            }
            $table[] = $option;
        }

        return $table;
    }
}
