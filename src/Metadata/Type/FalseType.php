<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

class FalseType extends ScalarType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): bool
    {
        if (false !== $value) {
            throw new InvalidTypeException($value, 'false');
        }

        return false;
    }
}
