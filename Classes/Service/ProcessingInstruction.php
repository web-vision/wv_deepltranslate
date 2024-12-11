<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Service;

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

final class ProcessingInstruction
{
    protected const PROCESSING_CACHE_IDENTIFIER = 'deepl-processing-cache';
    private FrontendInterface $runtimeCache;

    public function __construct(
        FrontendInterface $runtimeCache
    ) {
        $this->runtimeCache = $runtimeCache;
    }

    /**
     * @param int|string|null $id
     * @todo harden deeplMode being a pure boolean
     * @param string|bool $deeplMode
     */
    public function setProcessingInstruction(?string $table = null, $id = null, $deeplMode = false): void
    {
        $processingInformation = [
            'tableName' => $table,
            'id' => $id,
            'deeplMode' => $deeplMode,
        ];
        $this->runtimeCache->set(self::PROCESSING_CACHE_IDENTIFIER, $processingInformation);
    }

    /**
     * @return array{
     *     tableName: ?string,
     *     id: int|string|null,
     *     deeplMode: bool|string
     * }
     */
    public function getProcessingInstruction(): array
    {
        if (!$this->runtimeCache->has(self::PROCESSING_CACHE_IDENTIFIER)) {
            return [
                'tableName' => null,
                'id' => null,
                'deeplMode' => false,
            ];
        }
        return $this->runtimeCache->get(self::PROCESSING_CACHE_IDENTIFIER);
    }

    public function isDeeplMode(): bool
    {
        $processingInstructions = $this->getProcessingInstruction();

        return $processingInstructions['deeplMode'] === 'deepl' || $processingInstructions['deeplMode'] === true;
    }

    public function getProcessingTable(): ?string
    {
        $processingInstructions = $this->getProcessingInstruction();

        return $processingInstructions['tableName'];
    }

    /**
     * @return int|string|null
     */
    public function getProcessingId()
    {
        $processingInstructions = $this->getProcessingInstruction();

        return $processingInstructions['id'];
    }
}
