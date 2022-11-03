<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory\Type\Source;

use Guennichi\Mapper\Metadata\Type\TypeInterface;

interface ParameterTypeFactoryInterface
{
    public function create(\ReflectionParameter $reflectionParameter): ?TypeInterface;
}
