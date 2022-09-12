<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

class MissingArgumentsException extends MapperException
{
    /**
     * @param array<string> $missingArguments
     */
    public function __construct(string $classname, array $missingArguments, string $method = '__construct')
    {
        parent::__construct(sprintf('Missing arguments "%s" in "%s::%s"', implode(', ', $missingArguments), $classname, $method));
    }
}
