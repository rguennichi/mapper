<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;

class MixedType extends Type
{
    public function __toString(): string
    {
        return 'mixed';
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        return $input;
    }
}
