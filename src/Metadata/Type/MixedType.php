<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Model\Argument;

class MixedType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): mixed
    {
        return $value;
    }
}
