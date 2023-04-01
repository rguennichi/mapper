<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

class IntegerType extends ScalarType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): mixed
    {
        if (!\is_int($value)) {
            if ($argument->flexible && \is_scalar($value)) {
                return (int) $value;
            }

            throw new InvalidTypeException($value, 'int');
        }

        return $value;
    }
}
