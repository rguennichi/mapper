<?php

declare(strict_types=1);

namespace Tests\Guennichi\Mapper\Fixture;

use Guennichi\Mapper\Attribute\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class CustomAttribute extends Attribute
{
}
