<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Tests\Unit\Access;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use WebVision\Deepltranslate\Core\Access\AccessItemInterface;
use WebVision\Deepltranslate\Core\Access\AllowedGlossarySyncAccess;

class AllowedGlossarySyncAccessTest extends UnitTestCase
{
    private AllowedGlossarySyncAccess $accessInstance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accessInstance = new AllowedGlossarySyncAccess();
    }

    #[Test]
    public function hasInterfaceImplementation(): void
    {
        static::assertInstanceOf(AccessItemInterface::class, $this->accessInstance);
    }

    #[Test]
    public function getIdentifier(): void
    {
        static::assertSame('allowedGlossarySync', $this->accessInstance->getIdentifier());
    }

    #[Test]
    public function getTitle(): void
    {
        static::assertIsString($this->accessInstance->getTitle());
    }

    #[Test]
    public function getDescription(): void
    {
        static::assertIsString($this->accessInstance->getDescription());
    }

    #[Test]
    public function getIconIdentifier(): void
    {
        static::assertSame('deepl-logo', $this->accessInstance->getIconIdentifier());
    }
}
