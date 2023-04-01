<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

class FloatType extends ScalarType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): float
    {
        if (!\is_float($value) && !\is_int($value)) {
            if ($argument->flexible && \is_scalar($value)) {
                return (float) $value;
            }

            throw new InvalidTypeException($value, 'float');
        }

        return $value;
    }
}
