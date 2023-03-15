<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Traits;

use WebVision\WvDeepltranslate\Exception\GlossaryEntriesNotExistException;

trait GlossarySyncTrait
{
    private function syncSingleGlossary(int $uid): void
    {
        $glossaryInformation = $this->glossaryRepository
            ->getGlossaryInformationForSync($uid);

        if ($glossaryInformation['id'] !== '') {
            $this->deeplGlossaryService->deleteGlossary($glossaryInformation['id']);
        }

        try {
            $glossary = $this->deeplGlossaryService->createGlossary(
                $glossaryInformation['name'],
                $glossaryInformation['entries'],
                $glossaryInformation['source_lang'],
                $glossaryInformation['target_lang']
            );
        } catch (GlossaryEntriesNotExistException $exception) {
            $glossary = [];
        }

        $this->glossaryRepository->updateLocalGlossary($glossary, $uid);
    }
}
