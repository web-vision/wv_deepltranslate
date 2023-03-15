<?php

namespace WebVision\WvDeepltranslate\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Ricky Mathew <ricky@web-vision.de>, web-vision GmbH
 *      Anu Bhuvanendran Nair <anu@web-vision.de>, web-vision GmbH
 *
 *  You may not remove or change the name of the author above. See:
 *  http://www.gnu.org/licenses/gpl-faq.html#IWantCredit
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\DataHandling\DataHandlerCheckModifyAccessListHookInterface;

/**
 * TCEmainHook
 */
class TCEmainHook implements DataHandlerCheckModifyAccessListHookInterface
{
    /**
     * Manipulate the hook to make behave the 'localization' index in cmdmap array as a 'pseudo' table
     *
     * @param bool &$accessAllowed Whether the user has access to modify a table
     * @param string $table The name of the table to be modified
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parent The calling parent object
     */
    public function checkModifyAccessList(&$accessAllowed, $table, \TYPO3\CMS\Core\DataHandling\DataHandler $parent)
    {
        if ($table == 'localization') {
            $accessAllowed = true;
        }
    }
}
