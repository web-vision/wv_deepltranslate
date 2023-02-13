<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class GlossariesSync extends AbstractEntity
{
    /**
     * @var string
     */
    protected $glossaryId = '';

    /**
     * @var string
     */
    protected $sourceLang = '';

    /**
     * @var string
     */
    protected $targetLang = '';

    /**
     * @var string
     */
    protected $entries = '';

    public function getGlossaryId(): string
    {
        return $this->glossaryId;
    }

    public function setGlossaryId(string $glossaryId)
    {
        $this->glossaryId = $glossaryId;
    }

    public function getSourceLang(): string
    {
        return $this->sourceLang;
    }

    public function setSourceLang(string $sourceLang)
    {
        $this->sourceLang = $sourceLang;
    }

    public function getTargetLang(): string
    {
        return $this->targetLang;
    }

    public function setTargetLang(string $targetLang)
    {
        $this->targetLang = $targetLang;
    }

    public function getEntries(): string
    {
        return $this->entries;
    }

    public function setEntries(string $entries)
    {
        $this->entries = $entries;
    }
}
