<?php

declare(strict_types=1);

namespace Guennichi\Mapper;

use Guennichi\Mapper\Attribute\Attribute;
use Guennichi\Mapper\Metadata\Member\Parameter;

class Context
{
    /**
     * @var array<string, array<string, Parameter>>
     */
    private array $visits;

    public function __construct(
        private string $classname,
        private ?Parameter $parameter = null,
    ) {
    }

    public function visitClassname(string $classname): void
    {
        $this->visits[$classname] = [];
        $this->classname = $classname;
    }

    public function visitParameter(Parameter $parameter): void
    {
        $this->visits[$this->classname][$parameter->name] = $parameter;
        $this->parameter = $parameter;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    public function parameter(): ?Parameter
    {
        return $this->parameter;
    }

    /**
     * @return array<string, array<string, Parameter>>
     */
    public function visits(): array
    {
        return $this->visits;
    }

    public function attribute(string $attribute): ?Attribute
    {
        return $this->parameter?->attributes[$attribute] ?? null;
    }
}
