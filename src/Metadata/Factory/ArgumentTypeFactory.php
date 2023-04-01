<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Factory;

use Guennichi\Mapper\Metadata\Type\ArrayType;
use Guennichi\Mapper\Metadata\Type\CollectionType;
use Guennichi\Mapper\Metadata\Type\CompoundType;
use Guennichi\Mapper\Metadata\Type\MixedType;
use Guennichi\Mapper\Metadata\Type\NullableType;
use Guennichi\Mapper\Metadata\Type\TypeInterface;

class ArgumentTypeFactory
{
    public function __construct(
        private readonly ArgumentTypeFactoryInterface $docCommentTypeFactory,
        private readonly ArgumentTypeFactoryInterface $reflectionTypeFactory,
    ) {
    }

    public function __invoke(\ReflectionParameter $reflectionParameter): TypeInterface
    {
        $typeFromReflection = $this->reflectionTypeFactory->__invoke($reflectionParameter);

        if ($this->requiresDocCommentCheck($typeFromReflection)) {
            return $this->docCommentTypeFactory->__invoke($reflectionParameter);
        }

        return $typeFromReflection;
    }

    private function requiresDocCommentCheck(TypeInterface $type): bool
    {
        if ($type instanceof NullableType) {
            return $this->requiresDocCommentCheck($type->innerType);
        }

        if ($type instanceof CompoundType) {
            foreach ($type->types as $possibleType) {
                if (!$this->requiresDocCommentCheck($possibleType)) {
                    continue;
                }

                return true;
            }
        }

        if ($type instanceof CollectionType) {
            return $this->requiresDocCommentCheck($type->itemType);
        }

        return \in_array($type::class, [ArrayType::class, MixedType::class]);
    }
}
