<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Glossaries extends AbstractEntity
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

    /**
     * Get glossaryId
     *
     * @return string
     */
    public function getGlossaryId(): string
    {
        return $this->glossaryId;
    }

    /**
     * Set glossaryId
     *
     * @param string
     */
    public function setGlossaryId(string $glossaryId)
    {
        $this->glossaryId = $glossaryId;
    }

    /**
     * Get sourceLang
     *
     * @return string
     */
    public function getSourceLang(): string
    {
        return $this->sourceLang;
    }

    /**
     * Set sourceLang
     *
     * @param string
     */
    public function setSourceLang(string $sourceLang)
    {
        $this->sourceLang = $sourceLang;
    }

    /**
     * Get targetLang
     *
     * @return string
     */
    public function getTargetLang(): string
    {
        return $this->targetLang;
    }

    /**
     * Set targetLang
     *
     * @param string
     */
    public function setTargetLang(string $targetLang)
    {
        $this->targetLang = $targetLang;
    }

    /**
     * Get entries
     *
     * @return string
     */
    public function getEntries(): string
    {
        return $this->entries;
    }

    /**
     * Set entries
     *
     * @param string
     */
    public function setEntries(string $entries)
    {
        $this->entries = $entries;
    }
}
