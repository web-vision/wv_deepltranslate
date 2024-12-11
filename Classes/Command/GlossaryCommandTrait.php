<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Command;

use WebVision\Deepltranslate\Core\Domain\Repository\GlossaryRepository;
use WebVision\Deepltranslate\Core\Service\DeeplGlossaryService;

trait GlossaryCommandTrait
{
    protected DeeplGlossaryService $deeplGlossaryService;

    protected GlossaryRepository $glossaryRepository;

    public function injectDeeplGlossaryService(DeeplGlossaryService $deeplGlossaryService): void
    {
        $this->deeplGlossaryService = $deeplGlossaryService;
    }

    public function injectGlossaryRepository(GlossaryRepository $glossaryRepository): void
    {
        $this->glossaryRepository = $glossaryRepository;
    }
}
