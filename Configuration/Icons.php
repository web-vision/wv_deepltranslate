<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'actions-localize-deepl' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:deepltranslate_core/Resources/Public/Icons/actions-localize-deepl.svg',
    ],
    'deepl-grey-logo' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:deepltranslate_core/Resources/Public/Icons/deepl-grey.svg',
    ],
    'deepl-logo' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:deepltranslate_core/Resources/Public/Icons/deepl.svg',
    ],
];
