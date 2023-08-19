<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Metadata\Model\Argument;

/**
 * @template T of \BackedEnum
 *
 * @extends ObjectType<T>
 *
 * @property class-string<\BackedEnum> $classname
 */
class BackedEnumType extends ObjectType
{
    public function __construct(string $classname, public readonly IntegerType|StringType $backingType)
    {
        parent::__construct($classname);
    }

    public function resolve(mixed $value, Argument $argument): \BackedEnum
    {
        return $this->classname::from($this->backingType->resolve($value, $argument));
    }
}
