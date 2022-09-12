<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Exception;

use Guennichi\Mapper\Context;

class MissingArgumentsException extends MapperException
{
    /**
     * @param array<string> $missingArguments
     */
    public function __construct(string $classname, array $missingArguments, public readonly mixed $data, public readonly Context $context)
    {
        parent::__construct(sprintf('Missing arguments "%s" in "%s::__construct"', implode(', ', $missingArguments), $classname));
    }
}
