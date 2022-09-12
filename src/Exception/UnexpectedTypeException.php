<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

class UnexpectedTypeException extends MapperException
{
    public function __construct(mixed $value, bool|float|int|string|null $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type "%s", "%s" given', $expectedType, \is_object($value) ? \get_class($value) : get_debug_type($value)));
    }
}
