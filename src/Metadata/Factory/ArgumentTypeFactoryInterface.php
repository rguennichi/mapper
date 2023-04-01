<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Metadata\Type\TypeInterface;

interface ArgumentTypeFactoryInterface
{
    public function __invoke(\ReflectionParameter $reflectionParameter): TypeInterface;
}
