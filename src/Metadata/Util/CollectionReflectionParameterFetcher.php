<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Util;

class CollectionReflectionParameterFetcher
{
    public static function tryFetch(?\ReflectionType $type): ?\ReflectionParameter
    {
        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $classname = $type->getName();
        if (!is_subclass_of($classname, \Traversable::class)) {
            return null;
        }

        try {
            $targetReflectionConstructor = new \ReflectionMethod($classname, '__construct');
        } catch (\ReflectionException) {
            return null;
        }

        $targetParameters = $targetReflectionConstructor->getParameters();

        return 1 === \count($targetParameters) && ($targetParameters[0]->isVariadic() || 'array' === $targetParameters[0]->getType()?->__toString()) ?
            $targetParameters[0] :
            null;
    }
}
