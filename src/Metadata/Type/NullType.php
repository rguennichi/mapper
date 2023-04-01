<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

class NullType extends ScalarType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): mixed
    {
        if (null !== $value) {
            throw new InvalidTypeException($value, 'null');
        }

        return null;
    }
}
