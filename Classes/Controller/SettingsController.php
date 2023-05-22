<?php

declare(strict_types=1);

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
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\WvDeepltranslate\Domain\Model\Language;
use WebVision\WvDeepltranslate\Domain\Model\Settings;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;

/**
 * @deprecated Module is deprecated v10 and remove with v12
 */
class SettingsController extends ActionController
{
    protected PageRenderer $pageRenderer;

    protected SettingsRepository $settingsRepository;

    protected LanguageRepository $languageRepository;

    protected DeeplService $deeplService;

    public function injectPageRenderer(PageRenderer $pageRenderer)
    {
        $this->pageRenderer = $pageRenderer;
    }

    public function injectDeeplSettingsRepository(SettingsRepository $deeplSettingsRepository)
    {
        $this->settingsRepository = $deeplSettingsRepository;
    }

    public function injectLanguageRepository(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function injectDeeplService(DeeplService $deeplService)
    {
        $this->deeplService = $deeplService;
    }

    public function indexAction(): void
    {
        /** @var QueryResultInterface<Language> $sysLanguages */
        $sysLanguages = $this->languageRepository->findAll();
        if ($sysLanguages->count() === 0) {
            $this->addFlashMessage(
                'No system languages found.',
                'Errors system language',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }

        $preSelect = [];
        $settings = $this->settingsRepository->getSettings();
        if ($settings !== null) {
            $preSelect = array_filter($settings->getLanguagesAssigned());
        }

        $selectBox = $this->buildTableAssignments(
            $sysLanguages->toArray(),
            $preSelect
        );

        $typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
            \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
        );

        if (version_compare((string)$typo3VersionArray['version_main'], '11', '<')) {
            $massagesType = \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO;
        } else {
            $massagesType = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
        }

        $this->addFlashMessage(
            (string)LocalizationUtility::translate('LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:deprecated.deepl-be.body'),
            (string)LocalizationUtility::translate('LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:deprecated.deepl-be.title'),
            $massagesType
        );

        $this->view->assignMultiple([
            'sysLanguages' => $sysLanguages,
            'selectBox' => $selectBox,
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
        /** @var Settings|null $settings */
        $settings = $this->settingsRepository->getSettings();
        if ($settings === null) {
            $this->settingsRepository->insertDeeplSettings(
                0,
                unserialize($data['languages_assigned'])
            );
        } else {
            $this->settingsRepository->updateDeeplSettings(
                $settings->getUid(),
                $data['languages_assigned']
            );
        }

        $this->addFlashMessage(
            (string)LocalizationUtility::translate(
                'settings_success',
                'wv_deepltranslate'
            )
        );

        $this->redirect('index');
    }

    /**
     * return an array of options for multiple selectbox
     *
     * @param Language[] $sysLanguages
     * @param array<int, array{uid:int, pid:int, languages_assigned:string}> $preselectedValues
     *
     * @return array[]
     */
    public function buildTableAssignments(array $sysLanguages, array $preselectedValues): array
    {
        $table = [];

        $selectedKeys = array_keys($preselectedValues);
        foreach ($sysLanguages as $sysLanguage) {
            $option = $sysLanguage->toArray();
            if (in_array($sysLanguage->getUid(), $selectedKeys) || in_array(
                strtoupper($sysLanguage->getLanguageIsoCode()),
                $this->deeplService->apiSupportedLanguages
            )) {
                $option['value'] = $preselectedValues[$sysLanguage->getUid()] ?? strtoupper($sysLanguage->getLanguageIsoCode());
            }
            $table[] = $option;
        }

        return $table;
    }
}
