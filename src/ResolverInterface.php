<?php

declare(strict_types=1);

namespace Guennichi\Mapper;

use Guennichi\Mapper\Metadata\Type\TypeInterface;

interface ResolverInterface
{
    public function supports(TypeInterface $type): bool;

    public function resolve(mixed $input, TypeInterface $type, Context $context): mixed;
}
