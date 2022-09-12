<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Guennichi\Mapper\Exception\ResolverNotFoundException;

/** @internal */
class CompoundType extends AggregatedType
{
    public function __construct(TypeInterface ...$types)
    {
        parent::__construct($types, '|');
    }

    public function resolve(mixed $input, Context $context): mixed
    {
        throw new ResolverNotFoundException($this);
    }
}
