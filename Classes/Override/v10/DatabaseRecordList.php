<?php
namespace WebVision\WvDeepltranslate\Override\v10;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Compatible with v9 and v10
 *
 * Class for rendering of Web>List module
 */
class DatabaseRecordList extends \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
{
    /**
     * Creates the localization panel
     *
     * @param mixed[] $row The record for which to make the localization panel.
     * @return string[]
     */
    public function makeLocalizationPanel($table, $row): array
    {
        $out = parent::makeLocalizationPanel($table, $row);

        if (!empty($out[1])) {
            $uid = "'" . $row['uid'] . "'";
            $table = "'$table'";
            $lNew = sprintf(<<<HTML
                <a data-state="hidden" href="#" data-params="data[%s][%s][hidden]=0" >
                    <label class="btn btn-default btn-checkbox deepl-btn-wrap">
                        <input class="deepl-button" id="deepl-translation-enable-%s" type="checkbox" name="data[deepl.enable]" onclick="deeplTranslate(%s,%s)" />
                        <span></span>
                    </label>
                </a>
                HTML
                , '$table', '$ud', $row['uid'], $table, $uid);

            $out[1] .= $lNew;
        }

        return $out;
    }
}
