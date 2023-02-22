<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Traits;

trait GlossarySyncTrait
{
    private function syncSingleGlossary(int $uid): void
    {
        $glossaryInformation = $this->glossaryRepository
            ->getGlossaryInformationForSync($uid);

        if ($glossaryInformation['id'] !== '') {
            $this->deeplGlossaryService->deleteGlossary($glossaryInformation['id']);
        }
        $glossary = $this->deeplGlossaryService->createGlossary(
            $glossaryInformation['name'],
            $glossaryInformation['entries'],
            $glossaryInformation['source_lang'],
            $glossaryInformation['target_lang']
        );

        $this->glossaryRepository->updateLocalGlossary($glossary, $uid);
    }
}
