<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Util;

class ObjectFunctionNameGenerator
{
    public static function generate(string $prefix, string $classname): string
    {
        return $prefix . '_' . str_replace('\\', '_', $classname);
    }
}
