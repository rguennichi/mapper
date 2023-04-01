<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

class InvalidTypeException extends MapperException
{
    public function __construct(mixed $value, string $expectedType)
    {
        parent::__construct(sprintf('Expected value of type "%s", "%s" given: "%s"', $expectedType, get_debug_type($value), var_export($value, true)));
    }
}
