<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Tests\Unit\Access;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use WebVision\Deepltranslate\Core\Access\AccessItemInterface;
use WebVision\Deepltranslate\Core\Access\AllowedTranslateAccess;

class AllowedTranslateAccessTest extends UnitTestCase
{
    private AllowedTranslateAccess $accessInstance;

    protected function setUp(): void
    {
        $this->accessInstance = new AllowedTranslateAccess();
    }

    /**
     * @test
     */
    public function hasInterfaceImplementation(): void
    {
        static::assertInstanceOf(AccessItemInterface::class, $this->accessInstance);
    }

    /**
     * @test
     */
    public function getIdentifier(): void
    {
        static::assertSame('translateAllowed', $this->accessInstance->getIdentifier());
    }

    /**
     * @test
     */
    public function getTitle(): void
    {
        static::assertIsString($this->accessInstance->getTitle());
    }

    /**
     * @test
     */
    public function getDescription(): void
    {
        static::assertIsString($this->accessInstance->getDescription());
    }

    /**
     * @test
     */
    public function getIconIdentifier(): void
    {
        static::assertSame('deepl-logo', $this->accessInstance->getIconIdentifier());
    }
}
