<?php

namespace Minizord\Container\Interfaces;

use Closure;
use Minizord\Container\Interfaces\DefinitionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface {
    public function set(string $id, Closure|string|null $concrete = null, bool $shared = false): DefinitionInterface;
    public function singleton(string $id, Closure|string|null $concrete): void;
    public function instance(string $id, mixed $instance): void;
    public function hasInstance(string $id): bool;
    public function getInstance(string $id): mixed;
    public function getInstances(): array;
    public function hasDefinition(string $id): bool;
    public function getDefinition(string $id): DefinitionInterface;
    public function getDefinitions(): array;
    public function alias(string $id, string $alias): void;
    public function hasAlias(string $alias): bool;
    public function isAlias(string $name): bool;
    public function getAliases(): array;
    public function getAliasesById(string $id): array;
    public function getIdInContainer(string $aliasOrId): string;
    public function resolve(DefinitionInterface $definition): mixed;

}