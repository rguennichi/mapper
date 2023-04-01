<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

class TrueType extends ScalarType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): bool
    {
        if (true !== $value) {
            throw new InvalidTypeException($value, 'true');
        }

        return true;
    }
}
