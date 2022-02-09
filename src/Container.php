<?php

namespace Minizord\Container;
use Closure;
use Minizord\Container\Definition;
use Minizord\Container\Interfaces\ContainerInterface;
use Minizord\Container\Interfaces\DefinitionInterface;

class Container implements ContainerInterface {
    private array $aliases = [];
    private array $reverseAliases = [];
    private array $instances = [];
    private array $definitions = [];

    //PRINCIPAIS
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->definitions[$id]) || isset($this->aliases[$id]);
    }

    public function get(string $id): mixed
    {
        return null;
    }

    public function set(string $id, Closure|string|null $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $id;
        }

        if ($concrete instanceof Closure) {
            $this->definitions[$id] = (new Definition($id, null, $concrete, $shared));
        }

        if (is_string($concrete)) {
            $this->definitions[$id] = (new Definition($id, $concrete, null, $shared));
        }
    }
    
    public function singleton(string $id, Closure|string|null $concrete): void
    {
        $this->set($id, $concrete, true);
    }

    // INSTANCIAS
    public function instance(string $id, mixed $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function hasInstance(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    public function getInstance(string $id): mixed
    {
        return $this->instances[$id];
    }

    public function getInstances(): array
    {
        return $this->instances;
    }

    // DEFINIÇÕES
    public function hasDefinition(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    public function getDefinition(string $id): DefinitionInterface
    {
        return $this->definitions[$id];
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    // ID ALTERNATIVO
    public function alias(string $id, string $alias): void
    {
        $this->aliases[$alias] = $id;
        $this->reverseAliases[$id][] = $alias;
    }

    public function hasAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    public function isAlias(string $name): bool
    {
        return isset($this->aliases[$name]);
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getAliasesById(string $id): array
    {
        return $this->reverseAliases[$id];
    }

    public function getIdInContainer(string $aliasOrId): string
    {
        return isset($this->aliases[$aliasOrId])
            ? $this->aliases[$aliasOrId]
            : $aliasOrId;
    }

    // GERAL
    public function resolve(string $id): mixed
    {
        return null;
    }
}