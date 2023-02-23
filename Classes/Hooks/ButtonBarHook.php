<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

class ButtonBarHook
{
    protected PageRepository $pageRepository;

    public function __construct(?PageRepository $pageRepository = null)
    {
        $this->pageRepository = $pageRepository ?? GeneralUtility::makeInstance(PageRepository::class);
    }

    /**
     * @param array $params
     * @param ButtonBar $buttonBar
     *
     * @return array
     */
    public function getButtons(array $params, ButtonBar $buttonBar)
    {
        $buttons = $params['buttons'];
        $queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams();

        // do some initials
        $renderMode = '';
        $pageId = 0;
        $elementId = 0;
        $title = '';

        // we're inside a page
        if (isset($queryParams['id'])) {
            $page = BackendUtility::getRecord(
                'pages',
                $queryParams['id'],
                'uid,module'
            );
            if ($page['module'] === 'wv_deepltranslate') {
                $pageId = $page['uid'];
                $renderMode = DeeplBackendUtility::RENDER_TYPE_PAGE;
            }
        }

        // we are inside a glossary dataset
        if (
            isset($queryParams['edit'])
            && isset($queryParams['edit']['tx_wvdeepltranslate_glossary'])
        ) {
            $renderMode = DeeplBackendUtility::RENDER_TYPE_ELEMENT;
            $ids = array_keys($queryParams['edit']['tx_wvdeepltranslate_glossary']);
            $elementId = array_unshift($ids);
        }

        // no match on renderMode, exit
        if ($renderMode === '') {
            return $buttons;
        }

        switch ($renderMode) {
            case DeeplBackendUtility::RENDER_TYPE_ELEMENT:
                $params = $this->buildParamsForSingleEdit($elementId);
                $title = LocalizationUtility::translate(
                    'glossary.sync.button.single',
                    'wv_deepltranslate'
                );
                break;
            case DeeplBackendUtility::RENDER_TYPE_PAGE:
                $params = $this->buildParamsArrayForListView($pageId);
                $title = LocalizationUtility::translate(
                    'glossary.sync.button.all',
                    'wv_deepltranslate'
                );
                break;
        }

        // Style button
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $button = $buttonBar->makeLinkButton();
        $button->setIcon($iconFactory->getIcon(
            'apps-pagetree-folder-contains-glossar',
            Icon::SIZE_SMALL
        ));
        $button->setTitle($title);
        $button->setShowLabelText(true);

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uri = $uriBuilder->buildUriFromRoute(
            'glossaryupdate',
            $params
        );
        $button->setHref($uri);

        // Register Button and position it
        $buttons[ButtonBar::BUTTON_POSITION_LEFT][5][] = $button;

        return $buttons;
    }

    private function buildParamsArrayForListView(int $id): array
    {
        return [
            'uid' => $id,
            'mode' => DeeplBackendUtility::RENDER_TYPE_PAGE,
            'returnUrl' => $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri(),
        ];
    }

    private function buildParamsForSingleEdit(int $uid): array
    {
        return [
            'uid' => $uid,
            'mode' => DeeplBackendUtility::RENDER_TYPE_ELEMENT,
            'returnUrl' => $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri(),
        ];
    }
}
