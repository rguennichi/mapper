<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

class UnexpectedValueException extends UnexpectedTypeException
{
    public function __construct(mixed $value, string $expectedType)
    {
        parent::__construct($value, $expectedType);
    }
}
