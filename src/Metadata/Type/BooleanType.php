<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

class BooleanType extends ScalarType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): bool
    {
        if (!\is_bool($value)) {
            if ($argument->flexible) {
                return filter_var($value, \FILTER_VALIDATE_BOOL);
            }

            throw new InvalidTypeException($value, 'bool');
        }

        return $value;
    }
}
