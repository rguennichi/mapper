<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Attribute\Flexible;
use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

class StringType extends ScalarType
{
    public function __toString(): string
    {
        return 'string';
    }

    public function resolve(mixed $input, Context $context): string
    {
        if ($context->attribute(Flexible::class)) {
            /* @phpstan-ignore-next-line */
            return (string) $input;
        }

        if (!\is_string($input)) {
            throw new UnexpectedValueException($input, 'string', $context);
        }
        // We found that is_string() is a potential cause for performance issues
        return $input;
    }
}
