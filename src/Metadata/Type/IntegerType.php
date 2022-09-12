<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

/** @internal */
class IntegerType extends ScalarType
{
    public function __toString(): string
    {
        return 'int';
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        if ($context->flexible()) {
            return filter_var($input, \FILTER_VALIDATE_INT);
        }

        if (!\is_int($input)) {
            throw new UnexpectedValueException($input, 'int');
        }

        return $input;
    }
}
