<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Utility;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class DeeplBackendUtility
{
    public static function buildTranslateButton(
        $table,
        $id,
        $lUid_OnPage,
        $returnUrl,
        $languageTitle = '',
        $flagIcon = ''
    ): string {
        $redirectUrl = self::buildBackendRoute(
            'record_edit',
            [
                'justLocalized' => $table . ':' . $id . ':' . $lUid_OnPage,
                'returnUrl' => $returnUrl,
            ]
        );
        $params = [];
        $params['redirect'] = $redirectUrl;
        $params['cmd'][$table][$id]['localize'] = $lUid_OnPage;
        $params['cmd']['localization']['custom']['mode'] = 'deepl';
        $href = self::buildBackendRoute('tce_db', $params);
        $title =
            LocalizationUtility::translate(
                'backend.button.translate',
                'wv_deepltranslate',
                [
                    htmlspecialchars($languageTitle),
                ]
            );

        if ($flagIcon) {
            $icon = self::getIcon($flagIcon);
            $lC = $icon->render();
        } else {
            $lC = GeneralUtility::makeInstance(
                IconFactory::class
            )
                ->getIcon(
                    'actions-localize-deepl',
                    Icon::SIZE_SMALL
                )->render();
        }

        return '<a href="' . htmlspecialchars($href) . '"'
            . '" class="btn btn-default t3js-action-localize"'
            . ' title="' . $title . '">'
            . $lC . '</a> ';
    }

    public static function buildTranslateDropdown(
        $siteLanguages,
        $id,
        $requestUri
    ): string {
        $availableTranslations = [];
        foreach ($siteLanguages as $siteLanguage) {
            if (
                $siteLanguage->getLanguageId() === 0
                || $siteLanguage->getLanguageId() === -1
            ) {
                continue;
            }
            $availableTranslations[$siteLanguage->getLanguageId()] = $siteLanguage->getTitle();
        }
        // Then, subtract the languages which are already on the page:
        $localizationParentField = $GLOBALS['TCA']['pages']['ctrl']['transOrigPointerField'];
        $languageField = $GLOBALS['TCA']['pages']['ctrl']['languageField'];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(
                GeneralUtility::makeInstance(
                    WorkspaceRestriction::class,
                    (int)self::getBackendUserAuthentication()->workspace
                )
            );
        $statement = $queryBuilder->select('uid', $languageField)
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    $localizationParentField,
                    $queryBuilder->createNamedParameter($id, Connection::PARAM_INT)
                )
            )
            ->execute();
        while ($pageTranslation = $statement->fetchAssociative()) {
            unset($availableTranslations[(int)$pageTranslation[$languageField]]);
        }
        // If any languages are left, make selector:
        if (!empty($availableTranslations)) {
            $output = sprintf(
                '<option value="">%s</option>',
                htmlspecialchars(LocalizationUtility::translate('LLL:EXT:backend/Resources/Private/Language/locallang_layout.xlf:new_language'))
            );
            foreach ($availableTranslations as $languageUid => $languageTitle) {
                // Build localize command URL to DataHandler (tce_db)
                // which redirects to FormEngine (record_edit)
                // which, when finished editing should return back to the current page (returnUrl)
                $parameters = [
                    'justLocalized' => 'pages:' . $id . ':' . $languageUid,
                    'returnUrl' => $requestUri,
                ];
                $redirectUrl = self::buildBackendRoute('record_edit', $parameters);
                $params = [];
                $params['redirect'] = $redirectUrl;
                $params['cmd']['pages'][$id]['localize'] = $languageUid;
                $params['cmd']['localization']['custom']['mode'] = 'deepl';
                $targetUrl = self::buildBackendRoute('tce_db', $params);
                $output .= '<option value="' . htmlspecialchars($targetUrl) . '">' . htmlspecialchars($languageTitle) . '</option>';
            }

            return $output;
        }
        return '';
    }

    public static function buildBackendRoute(string $route, array $parameters): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute($route, $parameters);
    }

    private static function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    private static function getIcon(string $iconFlag): Icon
    {
        $deeplTranslateIcon = sprintf('deepl-translate-%s', $iconFlag);
        $newIcon = GeneralUtility::makeInstance(IconFactory::class)
            ->getIcon(
                $deeplTranslateIcon,
                Icon::SIZE_SMALL
            );

        if ($newIcon->getIdentifier() !== 'default-not-found') {
            return $newIcon;
        }
        $flagIcon = GeneralUtility::makeInstance(IconFactory::class)
            ->getIcon(
                $iconFlag,
                Icon::SIZE_SMALL
            );
        $deeplIcon = GeneralUtility::makeInstance(
            IconFactory::class
        )->getIcon(
            'actions-localize-deepl',
            Icon::SIZE_OVERLAY
        );
        GeneralUtility::makeInstance(IconRegistry::class)
            ->registerIcon(
                $deeplTranslateIcon,
                BitmapIconProvider::class,
            );

        $newIcon = GeneralUtility::makeInstance(IconFactory::class)
            ->getIcon(
                $deeplTranslateIcon,
                Icon::SIZE_SMALL
            );
        $newIcon->setIdentifier($deeplTranslateIcon);
        $newIcon->setMarkup($flagIcon->getMarkup());
        $newIcon->setOverlayIcon($deeplIcon);
        return $newIcon;
    }
}
