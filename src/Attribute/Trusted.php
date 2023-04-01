<?php

declare(strict_types=1);

namespace Guennichi\Mapper\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Trusted extends Attribute
{
}
