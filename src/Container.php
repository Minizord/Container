<?php

namespace Minizord\Container;

use Closure;
use ReflectionClass;
use Minizord\Container\Resolver;
use Minizord\Container\Definition;
use Minizord\Container\Exceptions\NotFoundException;
use Minizord\Container\Interfaces\ContainerInterface;
use Minizord\Container\Interfaces\DefinitionInterface;
use Minizord\Container\Exceptions\BindingResolutionException;

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
        if (!$this->has($id)) {
            if (class_exists($id)) {
                $this->set($id, $id);
                return $this->get($id);
            }
            throw new NotFoundException("Não ha nada registrado no container com id [$id], ou você passou uma classe que não existe para ser instanciada");
        }

        $id = $this->getIdInContainer($id);

        if (isset($this->instances[$id])) {
            return $this->getInstance($id);
        }

        $definition = $this->getDefinition($id);

        if($definition->isShared()) {
            $this->instances[$definition->getId()] = $this->resolve($definition);
            return $this->getInstance($definition->getId());
        }

        return $this->resolve($definition);
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

        // return $this->getDefinition($id);
    }

    public function singleton(string $id, Closure|string|null $concrete): void
    {
        $this->set($id, $concrete, true);
    }

    public function instance(string $id, mixed $instance): void
    {
        $this->instances[$id] = $instance;
    }

    // INSTANCIAS
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

    // #######################
    // # ID ALTERNATIVO
    // #######################

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

    // RESOLVER
    public function resolve(DefinitionInterface $definition): mixed
    {

        if ($definition->hasClosure()) {
            return $definition->getClosure()($this);
        }

        $reflector = $this->getReflectionClass($definition->getClass());

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return $reflector->newInstance();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflector->newInstanceArgs($dependencies);
    }

    private function getReflectionClass(string $class)
    {
        try {
            $class = new ReflectionClass($class);
            if (!$class->isInstantiable()) {
                throw new BindingResolutionException("Erro ao resolver [$this->id] a classe não é concreta, você só pode setar classes concretas e funções");
            }
            return $class;
        }
        catch (\Throwable $th) {
            throw new BindingResolutionException("Erro ao resolver [$this->id], a classe não existe, você só pode setar classes concretas e funções");
        }
    }

    private function resolveDependencies(array $dependencies): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            array_push($results, $this->get($dependency->getType()));
        }

        return $results;
    }
}