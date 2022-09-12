<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

use Guennichi\Mapper\Context;

class UnexpectedValueException extends UnexpectedTypeException
{
    public function __construct(public readonly mixed $value, string $expectedType, public readonly Context $context)
    {
        parent::__construct($value, $expectedType);
    }
}
