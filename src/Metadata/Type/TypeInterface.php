<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Exception\InvalidTypeException;
use Guennichi\Mapper\Metadata\Model\Argument;

interface TypeInterface
{
    /**
     * @throws InvalidTypeException
     */
    public function resolve(mixed $value, Argument $argument): mixed;
}
