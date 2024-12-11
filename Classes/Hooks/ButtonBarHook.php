<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Hooks;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\Deepltranslate\Core\Access\AllowedGlossarySyncAccess;

class ButtonBarHook
{
    /**
     * @param array<int|string, mixed> $params
     * @param ButtonBar $buttonBar
     *
     * @return array<int|string, mixed>
     * @throws RouteNotFoundException
     */
    public function getButtons(array $params, ButtonBar $buttonBar): array
    {
        $buttons = $params['buttons'];
        $queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams();

        if (!isset($queryParams['id']) || $queryParams['id'] === '0') {
            return $buttons;
        }

        /** @var array{uid: int, doktype?: int, module?: string}|null $page */
        $page = BackendUtility::getRecord(
            'pages',
            $queryParams['id'],
            'uid,module,doktype'
        );

        if (
            $page === null
            || (int)($page['doktype'] ?? 0) !== PageRepository::DOKTYPE_SYSFOLDER
            || ($page['module'] ?? '') !== 'glossary'
        ) {
            return $buttons;
        }

        if (!$this->getBackendUserAuthentication()->check('custom_options', AllowedGlossarySyncAccess::ALLOWED_GLOSSARY_SYNC)) {
            return $buttons;
        }

        $parameters = $this->buildParamsArrayForListView((int)$page['uid']);
        $title = (string)LocalizationUtility::translate(
            'glossary.sync.button.all',
            'DeepltranslateCore'
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
