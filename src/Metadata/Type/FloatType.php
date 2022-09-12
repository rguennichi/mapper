<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

class FloatType extends ScalarType
{
    public function __toString(): string
    {
        return 'float';
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        if ($context->attribute(Flexible::class)) {
            return filter_var($input, \FILTER_VALIDATE_FLOAT);
        }

        if (!\is_float($input) && !\is_int($input)) {
            throw new UnexpectedValueException($input, 'float|int', $context);
        }

        return $input;
    }
}
