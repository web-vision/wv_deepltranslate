<?php

declare(strict_types = 1);

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

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setTerm(string $term)
    {
        $this->term = $term;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }
}
