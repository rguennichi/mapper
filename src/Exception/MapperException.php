<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

class MapperException extends \RuntimeException
{
    public static function createFromReflectionParameter(string $message, \ReflectionParameter $reflectionParameter): self
    {
        return self::createFromClassnameArgument(
            $message,
            $reflectionParameter->getDeclaringClass()?->getName(),
            $reflectionParameter->getName(),
        );
    }

    public static function createFromClassnameArgument(string $message, ?string $classname, string $argument): self
    {
        return new self(sprintf('%s for "$%s" in "%s::__construct()"', $message, $argument, $classname));
    }
}
