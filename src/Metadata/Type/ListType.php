<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

abstract class ListType extends Type
{
    public function __construct(public readonly TypeInterface $valueType)
    {
    }
}
