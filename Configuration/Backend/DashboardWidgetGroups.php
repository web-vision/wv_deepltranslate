<?php

return (static function () {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('dashboard')) {
        return [
            'deepl' => [
                'title' => 'DeepL Widget',
            ],
        ];
    }

    return [];
})();
