<?php

namespace Minizord\Container\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface {
    public function getIdInContainer(string $aliasOrId): string;
    public function instance(string $id, mixed $instance): void;
    public function set(string $id, Closure|string|null $concrete = null, bool $shared = false): void;
    public function hasInstance(string $id): bool;
    public function getInstance(string $id): mixed;
    public function getInstances(): array;
    public function hasDefinition(string $id): bool;
    public function getDefinition(string $id): DefinitionInterface;
    public function getDefinitions(): array;
    public function alias(string $id, string $alias): void;
    public function isAlias(string $name): bool;
    public function getAliases(): array;
    public function getAliasesById(string $id): array;
    public function resolve(string $id): mixed;

}