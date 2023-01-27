<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ButtonBarHook
{
    protected PageRepository $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
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

        // $editArray = GeneralUtility::_GET('edit');
        // debug($editArray);

        $page = $this->pageRepository->getPage($queryParams['id']);

        if ($page['module'] === 'wv_deepltranslate') {
            // Style button
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $button = $buttonBar->makeLinkButton();
            $button->setIcon($iconFactory->getIcon(
                'apps-pagetree-folder-contains-glossar',
                Icon::SIZE_SMALL
            ));
            $button->setTitle('Sync Glossary');
            $button->setShowLabelText(true);

            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uri = $uriBuilder->buildUriFromRoute(
                'glossaryupdate',
                ['uid' => $queryParams['id']]
            );
            $button->setHref($uri);

            // Register Button and position it
            $buttons[ButtonBar::BUTTON_POSITION_LEFT][5][] = $button;
        }

        return $buttons;
    }
}
