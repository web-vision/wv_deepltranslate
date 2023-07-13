<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'apps-pagetree-folder-contains-glossary' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
    ],
    'actions-localize-deepl' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:wv_deepltranslate/Resources/Public/Icons/actions-localize-deepl.svg',
    ],
    'deepl-grey-logo' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl-grey.svg',
    ],
];
