<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Metadata\Type;

use Guennichi\Mapper\Context;
use Stringable;

/** @internal */
interface TypeInterface extends Stringable
{
    /**
     * @throws \TypeError
     */
    public function resolve(mixed $input, Context $context): mixed;
}
