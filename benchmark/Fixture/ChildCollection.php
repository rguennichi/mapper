<?php

declare(strict_types=1);

namespace Benchmark\Guennichi\Mapper\Fixture;

use Guennichi\Collection\Collection;

/**
 * @extends Collection<Child>
 */
class ChildCollection extends Collection
{
    public function __construct(Child ...$elements)
    {
        parent::__construct(...$elements);
    }
}
