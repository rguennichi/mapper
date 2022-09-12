<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

class BooleanType extends ScalarType
{
    public function __toString(): string
    {
        return 'bool';
    }

    public function resolve(mixed $input, Context $context): bool
    {
        if ($context->attribute(Flexible::class)) {
            return filter_var($input, \FILTER_VALIDATE_BOOL);
        }

        if (!\is_bool($input)) {
            throw new UnexpectedValueException($input, 'bool', $context);
        }

        return $input;
    }
}
