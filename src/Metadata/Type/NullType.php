<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\UnexpectedValueException;

/** @internal */
class NullType extends Type
{
    public function __toString(): string
    {
        return 'null';
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        if (null !== $input) {
            throw new UnexpectedValueException($input, 'null');
        }

        return null;
    }
}
