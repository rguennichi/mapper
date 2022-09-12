<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\ResolverNotFoundException;

/** @internal */
class CollectionType extends ListType
{
    public function __construct(public readonly string $classname, TypeInterface $valueType)
    {
        parent::__construct($valueType);
    }

    public function __toString(): string
    {
        return $this->classname . '<' . $this->valueType->__toString() . '>';
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
