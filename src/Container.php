<?php

namespace Minizord\Container;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use PHPUnit\Runner\Exception;
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
    private array $with = [];
    private array $buildStack = [];

    //PRINCIPAIS
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->definitions[$id]) || isset($this->aliases[$id]);
    }

    public function get(string $id, array $parameters = []): mixed
    {
        $this->with = $parameters;

        if (!$this->has($id)) {
            if (class_exists($id)) {
                $this->set($id, $id);
                return $this->get($id, $parameters);
            }
            $this->with = [];
            throw new NotFoundException("Não ha nada registrado no container com id [$id], ou você passou uma classe que não existe para ser instanciada");
        }

        $id = $this->getIdInContainer($id);

        if ($this->hasInstance($id)) {
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

        // se for função já executa
        if ($definition->hasClosure()) {
            return $definition->getClosure()($this, $this->getLastParameterOverride());
        }

        // pega a clase se nao der da um erro
        $reflector = $this->getReflectionClass($definition->getClass());

        // se não for instanciavel da erro
        if (!$reflector->isInstantiable()) {
            $this->notInstantiable($definition->getClass());
        }

        // adiciona o nome pra lista de resolução
        $this->addToBuildStack($definition->getClass());

        // pega constructor
        $constructor = $reflector->getConstructor();

        // se nao precisa de nd é só instancia
        if ($constructor === null) {
            $this->removeLastBuildStack();
            return $reflector->newInstance();
        }

        // pegando as dependencias
        $dependencies = $constructor->getParameters();

        // ai resolvemos elas
        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (BindingResolutionException $e) {
            $this->removeLastBuildStack();
            $this->resetWithParameters();
            throw $e;
        }

        // como termino esse removemos da lista
        $this->removeLastBuildStack();

        // instanciamos e retornamos
        return $reflector->newInstanceArgs($instances);
    }

    private function getReflectionClass(string $class)
    {
        try {
            $class = new ReflectionClass($class);
            return $class;
        }
        catch (\Throwable $th) {
            throw new BindingResolutionException("Erro ao resolver [$class], a classe não existe, você só pode setar classes concretas e funções");
        }
    }

    private function resolveDependencies(array $dependencies): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {

            // se o parametro já foi passado só pegamos e o retornamos
            if ($this->hasParameterOverride($dependency)) {
                if ($dependency->isVariadic()) {
                    $results = [...$results, ...$this->getParameterOverride($dependency)];
                    continue;
                }
                $results[] = $this->getParameterOverride($dependency);
                continue;
            }

            $result = is_null($this->getParameterClassName($dependency))
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);
            
            if ($dependency->isVariadic()) {
                if (!is_array($result)) {
                    $result = [$result];
                }
                $results = [...$results, ...$result];
            }
            else {
                $results[] = $result;
            }
        }

        return $results;
    }

    protected function getLastParameterOverride()
    {
        return count($this->with) ? $this->with : [];
    }

    private function notInstantiable(string $concrete): void
    {
        if (!empty($this->buildStack)) {
            $previous = implode(', ', $this->buildStack);
            $message = "Durante a construção de [$previous] a classe [$concrete] não é instanciável.";
        }
        else {
            $message = "A classe [$concrete] não é instanciável.";
        }

        throw new BindingResolutionException($message);
    }

    protected function hasParameterOverride(ReflectionParameter $dependency): bool
    {
        return isset($this->with[$dependency->getName()]);
    }

    protected function getParameterOverride(ReflectionParameter $dependency)
    {
        // return $this->getLastParameterOverride()[$dependency->name];
        return $this->with[$dependency->getName()];
    }

    public function getParameterClassName(ReflectionParameter $parameter): string|null
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                $previous = implode(', ', $this->buildStack);
                $message = "Durante a construção de [$previous] a classe [{$class->getName()}] precisa dela mesma para ser construída isso gera um loop infinito.";
                throw new BindingResolutionException($message);
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }

    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $message = "Impossível resolver a dependencia [$parameter] da classe [{$parameter->getDeclaringClass()->getName()}]";
        throw new BindingResolutionException($message);
    }

    protected function resolveClass(ReflectionParameter $parameter)
    {
 
            return $parameter->isVariadic()
                ? $this->resolveVariadicClass($parameter)
                : $this->get($this->getParameterClassName($parameter));
       

    }

    private function resolveVariadicClass(ReflectionParameter $parameter)
    {
        $className = $this->getParameterClassName($parameter);

        return $this->get($className);
    }

    private function addToBuildStack(string $stack): void
    {
        $this->buildStack[] = $stack;
    }

    private function removeLastBuildStack(): void
    {
        array_pop($this->buildStack);
    }

    private function resetWithParameters(): void
    {
        array_pop($this->with);
    }
}