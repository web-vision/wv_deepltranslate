<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Glossaries extends AbstractEntity
{
    /**
     * @var string
     */
    protected $definition = '';

    /**
     * @var string
     */
    protected $term = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * Get definition
     *
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * Set definition
     *
     * @param string
     */
    public function setDefinition(string $definition)
    {
        $this->definition = $definition;
    }

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
     * @param string
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
     * @param string
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
}
