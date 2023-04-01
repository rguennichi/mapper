<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Util;

class ClassnameDateTimeTypeChecker
{
    public static function isDateTime(string $classname): bool
    {
        return \DateTimeInterface::class === $classname || is_subclass_of($classname, \DateTimeInterface::class);
    }
}
