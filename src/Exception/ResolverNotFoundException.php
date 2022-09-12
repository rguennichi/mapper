<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

use Guennichi\Mapper\Metadata\Type\TypeInterface;

class ResolverNotFoundException extends MapperException
{
    public function __construct(TypeInterface $type)
    {
        parent::__construct(sprintf('Resolver not found for the type "%s"', $type->__toString()));
    }
}
