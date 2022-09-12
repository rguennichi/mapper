<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

/** @internal */
class BooleanType extends ScalarType
{
    public function __toString(): string
    {
        return 'bool';
    }

    public function resolve(mixed $input, Context $context): bool
    {
        if ($context->flexible()) {
            return filter_var($input, \FILTER_VALIDATE_BOOL);
        }

        if (!\is_bool($input)) {
            throw new UnexpectedValueException($input, 'bool');
        }

        return $input;
    }
}
