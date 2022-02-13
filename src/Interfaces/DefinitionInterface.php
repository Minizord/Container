<?php

namespace Minizord\Container\Interfaces;

use Closure;

interface DefinitionInterface {

    public function getId(): string;

    public function isShared(): bool;

    public function setShared(bool $shared): void;

    public function hasClosure(): bool;

    public function getClosure(): Closure;

    public function getClass(): string;

    public function when(string $needs, Closure|string|array $give): self;

    public function hasContextual(string $abstract): bool;
    
    public function getContextual(string $abstract): Closure|string|array;

}