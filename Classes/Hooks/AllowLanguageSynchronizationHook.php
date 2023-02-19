<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;

class AllowLanguageSynchronizationHook
{
    public function processDatamap_beforeStart(DataHandler $dataHandler): void
    {
        foreach ($dataHandler->datamap as $table => $elements) {
            foreach ($elements as $key => $element) {
                // element already exists, ignore
                if (MathUtility::canBeInterpretedAsInteger($key)) {
                    continue;
                }
                $l10nState = [];
                foreach ($element as $column => $value) {
                    $columnConfig = $GLOBALS['TCA'][$table]['columns'][$column];
                    if (
                        is_array($columnConfig['config']['behaviour'])
                        && array_key_exists('allowLanguageSynchronization', $columnConfig['config']['behaviour'])
                        && $columnConfig['config']['behaviour']['allowLanguageSynchronization']
                    ) {
                        if ($columnConfig['l10n_mode'] == 'prefixLangTitle') {
                            $l10nState[$column] = 'custom';
                        } else {
                            $l10nState[$column] = 'parent';
                        }
                    }
                }
                if (!empty($l10nState)) {
                    $element['l10n_state'] = json_encode($l10nState);
                    $dataHandler->datamap[$table][$key] = $element;
                }
            }
        }
    }
}
