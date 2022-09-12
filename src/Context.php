<?php

declare(strict_types=1);

namespace Guennichi\Mapper;

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
        private bool $flexible = false,
    ) {
    }

    public function visitClassname(string $classname): void
    {
        $this->visits[$classname] = [];
        $this->classname = $classname;
        $this->parameter = null;
    }

    public function visitParameter(Parameter $parameter): void
    {
        $this->visits[$this->classname][$parameter->name] = $parameter;
        $this->parameter = $parameter;
        $this->flexible = $parameter->flexible;
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

    public function flexible(): bool
    {
        return $this->flexible;
    }
}
