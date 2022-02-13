<?php

namespace Minizord\Container;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
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

    /**
     * Checka se existe serviço pelo id
     *
     * @param string  $id  Id ou id alternativo do serviço
     * @return boolean
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->definitions[$id]) || $this->hasAlias($id);
    }

    /**
     * Retorna a construção de um serviço pelo id ou id alternativo
     *
     * @param string  $id          Id do serviço para construir
     * @param array   $parameters  Parametros para ser usado na construção do serviço
     * @return mixed
     */
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

    /**
     * Seta um serviço no container
     *
     * @param string                    $id        Id do serviço para identifica-lo no container
     * @param Closure|string|null|null  $concrete  Classe, função para ser construida/executada
     * @param boolean                   $shared    Para tornar o serviço um singleton
     * @return DefinitionInterface
     */
    public function set(string $id, Closure|string|null $concrete = null, bool $shared = false): DefinitionInterface
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

        return $this->getDefinition($id);
    }


    /**
     * Seta um serviço já como singleton no container
     *
     * @param string               $id        Id do serviço para identifica-lo no container
     * @param Closure|string|null  $concrete  Classe, função para ser construida/executada
     * @return void
     */
    public function singleton(string $id, Closure|string|null $concrete): void
    {
        $this->set($id, $concrete, true);
    }

    /**
     * Seta uma instancia diretamente no container podendo ser qualquer coisa mas será retornada exatamente como foi setada
     *
     * @param string  $id
     * @param mixed   $instance  Qualquer coisa que deseja retornar exatamente como está
     * @return void
     */
    public function instance(string $id, mixed $instance): void
    {
        $this->instances[$id] = $instance;
    }

    // INSTANCIAS

    /**
     * Checka se existe uma instância no container pelo id
     *
     * @param string  $id  Id da instância 
     * @return boolean
     */
    public function hasInstance(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    /**
     * Retorna uma instância do container pelo id
     *
     * @param string  $id
     * @return mixed
     */
    public function getInstance(string $id): mixed
    {
        return $this->instances[$id];
    }

    /**
     * Retorna todas as instâncias setadas no container
     *
     * @return array
     */
    public function getInstances(): array
    {
        return $this->instances;
    }

    // DEFINIÇÕES

    /**
     * Checka se uma definião existe no container pelo id
     *
     * @param string  $id  Id da definição
     * @return boolean
     */
    public function hasDefinition(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * Retorna uma definição do container pelo id
     *
     * @param string  $id  Id da definição
     * @return DefinitionInterface
     */
    public function getDefinition(string $id): DefinitionInterface
    {
        return $this->definitions[$id];
    }

    /**
     * Retorna todas as definições setadas no container
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    // ID ALTERNATIVO

    /**
     * Seta um id alternativo (alias)
     *
     * @param string  $id     Id final, o qual o id alternativo (alias) irá representar
     * @param string  $alias  Id alternativo (alias), que representará um id fnal
     * @return void
     */
    public function alias(string $id, string $alias): void
    {
        $this->aliases[$alias] = $id;
        $this->reverseAliases[$id][] = $alias;
    }

    /**
     * Checka se existe um id alternativo (alias)
     *
     * @param string  $alias Id alternativo (alias)
     * @return boolean
     */
    public function hasAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Check se o texto passado é um id alternativo (alias)
     *
     * @param string  $name  Nome a ser verificado se é um id alternativo (alias)
     * @return boolean
     */
    public function isAlias(string $name): bool
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Retorna todos os id alternativos (aliases)
     *
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Retorna todos os id alternativos que existem para um determinado id final
     *
     * @param string  $id  Id final a ter seus id alternativos retornados
     * @return array
     */
    public function getAliasesById(string $id): array
    {
        return $this->reverseAliases[$id];
    }

    /**
     * Retorna o id final dentro do container por um id alternativo desse id
     *
     * @param string  $aliasOrId Id final ou id alternativo para buscar o seu id final
     * @return string
     */
    public function getIdInContainer(string $aliasOrId): string
    {
        return isset($this->aliases[$aliasOrId])
            ? $this->aliases[$aliasOrId]
            : $aliasOrId;
    }

    // RESOLVER

    /**
     * Resolve uma Definition retornando seu resultado final seja uma classe ou execução de uma função
     *
     * @param DefinitionInterface  $definition  Definição para ser resolvida
     * @return mixed
     */
    public function resolve(DefinitionInterface $definition): mixed
    {

        // se for função já executa
        if ($definition->hasClosure()) {
            return $definition->getClosure()($this, $this->geAllParameterOverride());
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
            $instances = $this->resolveDependencies($dependencies, $definition);
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

    /**
     * Retorna o ReflectionClass de uma classe
     *
     * @param string  $class  Classe a ser retornada como ReflectionClass
     * @return ReflectionClass
     */
    private function getReflectionClass(string $class): ReflectionClass
    {
        try {
            $class = new ReflectionClass($class);
            return $class;
        }
        catch (\Throwable $th) {
            throw new BindingResolutionException("Erro ao resolver [$class], a classe não existe, você só pode setar classes concretas e funções");
        }
    }

    /**
     * Resolve um array de depndencias
     *
     * @param array                $dependencies  Dependências
     * @param DefinitionInterface  $definition    Definição do serviço
     * @return array
     */
    private function resolveDependencies(array $dependencies, DefinitionInterface $definition): array
    {

        $results = [];

        foreach ($dependencies as $dependency) {

            // se o parametro já foi passado só pegamos e o retornamos
            if($result = $this->getResultInWithParameters($dependency, $results)) {
                $results = $result;
                continue;
            }

            if ($result = $this->getResultInContextuals($dependency, $definition, $results)) {
                $results = $result;
                continue;
            }
            
            if($result = $this->getResultInNormalResolver($dependency, $results)) {
                $results = $result;
                continue;
            }
            
        }

        return $results;
    }


    /**
     * Retornas todos os parametros passados no get()
     *
     * @return void
     */
    protected function geAllParameterOverride(): array
    {
        return count($this->with) ? $this->with : [];
    }

    /**
     * Retorna um erro que a classe não é instânciavel, com a devida mensagem de erro
     *
     * @param string  $concrete  Classe para verificar
     * @return void
     */
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


    /**
     * Retorna se há uma parametro passado no get() para essa dependência
     *
     * @param ReflectionParameter  $dependency  Parametro/dependência de uma classe
     * @return boolean
     */
    protected function hasParameterOverride(ReflectionParameter $dependency): bool
    {
        return isset($this->with[$dependency->getName()]);
    }

    /**
     * Retorna determinado parâmetro que foi passado no get()
     *
     * @param ReflectionParameter  $dependency  Parâmetro/dependência de uma classe
     * @return void
     */
    protected function getParameterOverride(ReflectionParameter $dependency)
    {
        return $this->with[$dependency->getName()];
    }

    /**
     * Retorna a classe de um determinado parâmetro
     *
     * @param ReflectionParameter  $parameter  Parâmetro para pegar sua classe
     * @return string|null
     */
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

    /**
     * Resolve um parâmetro primitivo
     *
     * @param ReflectionParameter  $parameter  Parâmetro a ser resolvido
     * @return void
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $message = "Impossível resolver a dependencia [$parameter] da classe [{$parameter->getDeclaringClass()->getName()}]";
        throw new BindingResolutionException($message);
    }

    /**
     * Resolve a classe de um parâmetro, retornando sua instância
     *
     * @param ReflectionParameter  $parameter  Parâmetro que terá sua classe devolvida
     * @return void
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        return $this->get($this->getParameterClassName($parameter));
    }

    /**
     * Adiciona uma classe ao historico de resoluções de classes, para ter uma caminho onde determinada dependência falhou
     *
     * @param string  $stack  Classe a ser colocada na lista
     * @return void
     */
    private function addToBuildStack(string $stack): void
    {
        $this->buildStack[] = $stack;
    }

    /**
     * Remove o último item da lista de resoluções
     *
     * @return void
     */
    private function removeLastBuildStack(): void
    {
        array_pop($this->buildStack);
    }

    /**
     * Reseta os parâmetros passados pelo get()
     *
     * @return void
     */
    private function resetWithParameters(): void
    {
        $this->with = [];
    }

    /**
     * Buscar resolver uma dependência por meio do próprio container com get()
     *
     * @param ReflectionParameter  $dependency  Dependência a ser resolvida
     * @param array                $results     É o resultado da dependência pode ser desde uma classe até um array
     * @return mixed
     */
    private function getResultInNormalResolver(ReflectionParameter $dependency, array $results): mixed
    {
        $result = null;

        $result = is_null($this->getParameterClassName($dependency))
            ? $this->resolvePrimitive($dependency)
            : $this->resolveClass($dependency);

        if (!$dependency->isVariadic()) {
            $results[] = $result;
            return $results;
        }

        if (!is_array($result)) {
            $result = [$result];
        }

        $results = [...$results, ...$result];
        return $results;
    }

    /**
     * Buscar resolver uma dependência por meio dos parâmetros passado no get()
     *
     * @param ReflectionParameter  $dependency  Dependência a ser resolvida
     * @param array                $results     É o resultado da dependência pode ser desde uma classe até um array
     * @return mixed
     */
    private function getResultInWithParameters(ReflectionParameter $dependency, array $results): mixed
    {
        $result = null;

        if ($this->hasParameterOverride($dependency)) {
            $result = $this->getParameterOverride($dependency);
        }

        if (!is_null($result)) {
            if (!$dependency->isVariadic()) {
                $results[] = $result;
                return $results;
            }

            if (!is_array($result)) {
                $result = [$result];
            }

            $results = [...$results, ...$result];
            return $results;
        }

        return $result;
    }

    /**
     * Buscar resolver uma dependência por meio do contexto definido em uma definição (Definition)
     *
     * @param ReflectionParameter  $dependency  Dependência a ser resolvida
     * @param Definition           $definition  Definição (Definition) para verificar se tem o contexto para essa depenência
     * @param array                $results     É o resultado da dependência pode ser desde uma classe até um array
     * @return mixed
     */
    private function getResultInContextuals(ReflectionParameter $dependency, Definition $definition, array $results): mixed
    {
        $result = null;

        $className = $this->getParameterClassName($dependency);

        if(is_null($className)) {
            if ($definition->hasContextual($dependency->getName())) {
                $result =  $this->get($definition->getContextual($dependency->getName()));
            }
        } 
        else {
            if ($definition->hasContextual($className)) {
                $contextual = $definition->getContextual($className);

                if(!is_array($contextual)) {
                    $result = $this->get($contextual);
                }
                else {
                    $result = array_map(fn($classForBuild) => $this->get($classForBuild), $contextual);
                }
            }
        }

        if (!is_null($result)) {
            if (!$dependency->isVariadic()) {
                $results[] = $result;
                return $results;
            }

            if (!is_array($result)) {
                $result = [$result];
            }

            $results = [...$results, ...$result];
            return $results;
        }

        return $result;
    }

    /**
     * Seta um parâmetro dentro do container
     *
     * @param string                        $id     Id do parâmetro para identifica-ló
     * @param string|integer|float|boolean  $value  Valor do parâmetro, o que será retornado
     * @return void
     */
    public function setParameter(string $id, string|int|float|bool $value): void
    {
        $this->parameters[$id] = $value;
    }

    /**
     * Verifica se um parâmetro existe no container por seu id
     *
     * @param string  $id  Id do parâmetro
     * @return boolean
     */
    public function hasParameter(string $id): bool
    {
        return isset($this->parameters[$id]);
    }

    /**
     * Retorna um parâmetro do container por seu id
     *
     * @param string  $id  Id do parâmetro
     * @return string|int|float|bool
     */
    public function getParameter(string $id): string|int|float|bool
    {
        return $this->parameters[$id];
    }
}