<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

use Guennichi\Collection\Collection;

/**
 * @extends Collection<Something>
 */
class SomethingCollection extends Collection
{
    public function __construct(Something ...$elements)
    {
        parent::__construct(...$elements);
    }
}
