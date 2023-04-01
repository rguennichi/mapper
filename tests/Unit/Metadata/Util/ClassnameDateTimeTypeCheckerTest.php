<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Unit\Metadata\Util;

use Guennichi\Mapper\Metadata\Util\ClassnameDateTimeTypeChecker;
use PHPUnit\Framework\TestCase;

class ClassnameDateTimeTypeCheckerTest extends TestCase
{
    public function testItReturnsTrueIfClassnameIsDateTime(): void
    {
        self::assertTrue(ClassnameDateTimeTypeChecker::isDateTime(\DateTime::class));
        self::assertTrue(ClassnameDateTimeTypeChecker::isDateTime(\DateTimeInterface::class));
        self::assertTrue(ClassnameDateTimeTypeChecker::isDateTime(\DateTimeImmutable::class));
        self::assertTrue(ClassnameDateTimeTypeChecker::isDateTime(CustomDateTime::class));

        self::assertFalse(ClassnameDateTimeTypeChecker::isDateTime(\stdClass::class));
        self::assertFalse(ClassnameDateTimeTypeChecker::isDateTime('example'));
    }
}

class CustomDateTime extends \DateTimeImmutable
{
}
