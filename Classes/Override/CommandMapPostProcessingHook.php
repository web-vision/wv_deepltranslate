<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Override;

use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class takes care of content translation for elements within containers
 */
class CommandMapPostProcessingHook extends \B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook
{
    protected function localizeOrCopyToLanguage(int $uid, int $language, string $command, DataHandler $dataHandler): void
    {
        try {
            $container = $this->containerFactory->buildContainer($uid);
            $children = $container->getChildRecords();
            $cmd = ['tt_content' => []];
            //injecting custom localization flag into cmd
            if (!empty($dataHandler->cmdmap['localization'])) {
                $cmd['localization'] = $dataHandler->cmdmap['localization'];
            }
            foreach ($children as $colPos => $record) {
                $cmd['tt_content'][$record['uid']] = [$command => $language];
            }
            if (count($cmd['tt_content']) > 0) {
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $localDataHandler->start([], $cmd, $dataHandler->BE_USER);
                $localDataHandler->process_cmdmap();
            }
        } catch (Exception $e) {
            // exception is expected, if CE is not a container
        }
    }
}
