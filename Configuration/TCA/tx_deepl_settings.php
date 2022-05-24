<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2020 Ricky Mathew Kuruvilla <ricky@web-vision.de>
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

$GLOBALS['TCA']['tx_deepl_settings'] = [
    'ctrl' => [
        'title' => 'Deepl settings',
        'label' => 'uid',
        'iconfile' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
        'hideTable' => 1,
        'rootLevel' => 1,
    ],
    'columns' => [
        'languages_assigned' => [
            'label' => 'Deepl language assignments',
            'config' => [
                'type' => 'input',
                'eval' => '',
            ],
        ],
    ],
    'types' => [
        0 => [
            'showitem' => '',
        ],
    ],
];
