<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture\Attribute;

use Guennichi\Mapper\Attribute\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class Internal extends Attribute
{
}
