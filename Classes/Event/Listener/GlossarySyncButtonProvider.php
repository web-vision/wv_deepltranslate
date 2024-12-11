<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Event\Listener;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\Deepltranslate\Core\Access\AllowedGlossarySyncAccess;

final class GlossarySyncButtonProvider
{
    private const TABLE_NAME = 'tx_wvdeepltranslate_glossaryentry';

    private const ALLOWED_MODULES = [
        'web_layout',
        'web_list',
    ];

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        $buttons = $event->getButtons();
        $request = $this->getRequest();

        $requestParams = $request->getQueryParams();

        $id = (int)($requestParams['id'] ?? 0);
        $module = $request->getAttribute('module');
        $normalizedParams = $request->getAttribute('normalizedParams');
        $pageTSconfig = BackendUtility::getPagesTSconfig($id);

        $page = BackendUtility::getRecord(
            'pages',
            $id,
            'uid,module'
        );

        if (!$id
            || $module === null
            || $normalizedParams === null
            || !empty($pageTSconfig['mod.']['SHARED.']['disableSysNoteButton'])
            || !$this->canCreateNewRecord($id)
            || !in_array($module->getIdentifier(), self::ALLOWED_MODULES, true)
            || ($module->getIdentifier() === 'web_list' && !$this->isCreationAllowed($pageTSconfig['mod.']['web_list.'] ?? []))
            || !isset($page['module'])
            || $page['module'] !== 'glossary'
        ) {
            return;
        }

        if (!$this->getBackendUserAuthentication()->check('custom_options', AllowedGlossarySyncAccess::ALLOWED_GLOSSARY_SYNC)) {
            return;
        }

        $parameters = $this->buildParamsArrayForListView((int)$id);
        $title = (string)LocalizationUtility::translate(
            'glossary.sync.button.all',
            'DeepltranslateCore'
        );
        // Style button
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $button = $event->getButtonBar()->makeLinkButton();
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
        $button->setHref((string)$uri);

        // Register Button and position it
        $buttons[ButtonBar::BUTTON_POSITION_LEFT][5][] = $button;

        $event->setButtons($buttons);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @param array<int|string, mixed> $modTSconfig
     */
    protected function isCreationAllowed(array $modTSconfig): bool
    {
        $allowedNewTables = GeneralUtility::trimExplode(',', $modTSconfig['allowedNewTables'] ?? '', true);
        $deniedNewTables = GeneralUtility::trimExplode(',', $modTSconfig['deniedNewTables'] ?? '', true);

        return ($allowedNewTables === [] && $deniedNewTables === [])
            || (!in_array(self::TABLE_NAME, $deniedNewTables)
                && ($allowedNewTables === [] || in_array(self::TABLE_NAME, $allowedNewTables)));
    }

    protected function canCreateNewRecord(int $id): bool
    {
        $tableConfiguration = $GLOBALS['TCA'][self::TABLE_NAME]['ctrl'];
        $pageRow = BackendUtility::getRecord('pages', $id);
        $backendUser = $this->getBackendUserAuthentication();

        return !($pageRow === null
            || ($tableConfiguration['readOnly'] ?? false)
            || ($tableConfiguration['hideTable'] ?? false)
            || ($tableConfiguration['is_static'] ?? false)
            || (($tableConfiguration['adminOnly'] ?? false) && !$backendUser->isAdmin())
            || !$backendUser->doesUserHaveAccess($pageRow, Permission::CONTENT_EDIT)
            || !$backendUser->check('tables_modify', self::TABLE_NAME)
            || !$backendUser->workspaceCanCreateNewRecord(self::TABLE_NAME));
    }

    /**
     * @return array{uid: int, returnUrl: string|UriInterface}
     */
    private function buildParamsArrayForListView(int $id): array
    {
        return [
            'uid' => $id,
            'returnUrl' => $this->getRequest()->getAttribute('normalizedParams')->getRequestUri(),
        ];
    }
}
