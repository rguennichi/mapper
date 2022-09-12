<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedTypeException;

/** @internal */
class StringType extends ScalarType
{
    public function __toString(): string
    {
        return 'string';
    }

    public function resolve(mixed $input, Context $context): string
    {
        if ($context->flexible()) {
            /* @phpstan-ignore-next-line */
            return (string) $input;
        }

        if (!\is_string($input)) {
            throw new UnexpectedTypeException($input, 'string');
        }
        // We found that is_string() is a potential cause for performance issues
        return $input;
    }
}
