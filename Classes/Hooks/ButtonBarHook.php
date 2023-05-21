<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ButtonBarHook
{
    /**
     * @param array $params
     * @param ButtonBar $buttonBar
     *
     * @return array
     * @throws RouteNotFoundException
     */
    public function getButtons(array $params, ButtonBar $buttonBar)
    {
        $buttons = $params['buttons'];
        $queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams();

        // we're inside a page
        if (isset($queryParams['id'])) {
            $page = BackendUtility::getRecord(
                'pages',
                $queryParams['id'],
                'uid,module'
            );

            if (
                isset($page['module']) && $page['module'] === 'glossary'
                && $this->getBackendUserAuthentication()
                    ->check('tables_modify', 'tx_wvdeepltranslate_glossaryentry')
            ) {
                $parameters = $this->buildParamsArrayForListView($page['uid']);
                $title = (string)LocalizationUtility::translate(
                    'glossary.sync.button.all',
                    'wv_deepltranslate'
                );
                // Style button
                $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                $button = $buttonBar->makeLinkButton();
                $button->setIcon($iconFactory->getIcon(
                    'apps-pagetree-folder-contains-glossary',
                    Icon::SIZE_SMALL
                ));
                $button->setTitle($title);
                $button->setShowLabelText(true);

                $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                $uri = $uriBuilder->buildUriFromRoute(
                    'glossaryupdate',
                    $parameters
                );
                $button->setHref($uri);

                // Register Button and position it
                $buttons[ButtonBar::BUTTON_POSITION_LEFT][5][] = $button;
            }
        }

        return $buttons;
    }

    private function buildParamsArrayForListView(int $id): array
    {
        return [
            'uid' => $id,
            'returnUrl' => $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri(),
        ];
    }

    private function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
