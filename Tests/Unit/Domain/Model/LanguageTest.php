<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use WebVision\WvDeepltranslate\Domain\Model\Language;

/**
 * @covers \WebVision\WvDeepltranslate\Domain\Model\Language
 */
class LanguageTest extends UnitTestCase
{
    /** @test */
    public function hasExtbaseAbstractEntity(): void
    {
        $subject = new Language();
        static::assertInstanceOf(AbstractEntity::class, $subject);
    }

    public function classAttributeDataProvider(): array
    {
        return [
            ['title', 'The entity "Language" field has not the attribute "title"'],
        ];
    }

    /**
     * @dataProvider classAttributeDataProvider
     * @test
     */
    public function hasClassProperties(string $attribute, string $message): void
    {
        static::assertTrue(property_exists(Language::class, $attribute), $message);
    }

    public function classMethodDataProvider(): array
    {
        return [
            ['toArray', 'The entity "Language" has not the method "toArray"'],
        ];
    }

    /**
     * @dataProvider classMethodDataProvider
     * @test
     */
    public function hasClassFunction(string $methodName, string $message)
    {
        static::assertTrue(method_exists(Language::class, $methodName), $message);
    }
}
