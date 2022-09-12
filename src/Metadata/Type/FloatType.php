<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

/** @internal */
class FloatType extends ScalarType
{
    public function __toString(): string
    {
        return 'float';
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        if ($context->flexible()) {
            return filter_var($input, \FILTER_VALIDATE_FLOAT);
        }

        if (!\is_float($input)) {
            throw new UnexpectedValueException($input, 'float');
        }

        return $input;
    }
}
