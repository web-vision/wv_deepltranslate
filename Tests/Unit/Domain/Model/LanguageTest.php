<?php declare(strict_types = 1);

namespace Unit\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
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
        static::assertClassHasAttribute($attribute, Language::class, $message);
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
