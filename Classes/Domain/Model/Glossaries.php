<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Glossaries extends AbstractEntity
{
    /**
     * @var string
     */
    protected string $term = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * Get term
     *
     * @return string
     */
    public function getTerm(): string
    {
        return $this->term;
    }

    /**
     * Set term
     *
     * @param string $term
     */
    public function setTerm(string $term)
    {
        $this->term = $term;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
}
