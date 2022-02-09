<?php

namespace Minizord\Container;
use Closure;
use Minizord\Container\Interfaces\DefinitionInterface;

class Definition implements DefinitionInterface {
    public function __construct(
        private string $id, 
        private string|null $class,
        private Closure|null $closure = null,
        private bool $shared = false,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function setShared(bool $shared): void
    {
        $this->shared = $shared;
    }
    
    public function hasClosure(): bool
    {
        return isset($this->closure);
    }
}