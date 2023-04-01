<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Name extends Attribute
{
    public function __construct(public readonly string $name)
    {
    }
}
