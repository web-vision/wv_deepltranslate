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
                    if (!isset($GLOBALS['TCA'][$table]['columns'][$column])) {
                        continue;
                    }

                    $columnConfig = $GLOBALS['TCA'][$table]['columns'][$column];

                    if (isset($columnConfig['config']['behaviour'])
                        && is_array($columnConfig['config']['behaviour'])
                        && isset($columnConfig['config']['behaviour']['allowLanguageSynchronization'])
                        && (bool)$columnConfig['config']['behaviour']['allowLanguageSynchronization'] === true
                    ) {
                        $l10nState[$column] = (($columnConfig['l10n_mode'] ?? '') === 'prefixLangTitle')
                            ? 'custom'
                            : 'parent';
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
