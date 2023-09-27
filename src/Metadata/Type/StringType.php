<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

class StringType extends ScalarType implements TypeInterface
{
    public function resolve(mixed $value, Argument $argument): string
    {
        if (!\is_string($value)) {
            if ($argument->flexible && (null === $value || \is_scalar($value))) {
                return (string) $value;
            }

            throw new InvalidTypeException($value, 'string');
        }

        return $value;
    }
}
