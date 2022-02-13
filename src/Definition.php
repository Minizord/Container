<?php

namespace Minizord\Container;

use Closure;
use Minizord\Container\Interfaces\DefinitionInterface;
use Minizord\Container\Exceptions\DefinitionException;

class Definition implements DefinitionInterface {

    private array $contextual = [];

    /**
     * Construtor
     *
     * @param string        $id       Identificador do serviço, geralmente uma interface ou a própria classe
     * @param string|null   $class    A classe concreta que será instanciada ao resolver essa definição
     * @param Closure|null  $closure  Função que será retornada ao resolver essa definição
     * @param boolean       $shared   Define se será compartilhado ou seja um singleton
     */
    public function __construct(
        private string $id, 
        private string|null $class,
        private Closure|null $closure = null,
        private bool $shared = false,
    ) {}

    /**
     * Retorna o próprio ID
     *
     * @return string  ID do serviço
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Retorna se o serviço é um singleton ou não
     *
     * @return boolean
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * Tranforma esse serviço em um singleton ou não
     *
     * @param boolean  $shared  Determina se será um singleton
     * @return void
     */
    public function setShared(bool $shared): void
    {
        $this->shared = $shared;
    }
    
    /**
     * Retorna se esse servio é uma função (Closure)
     *
     * @return boolean
     */
    public function hasClosure(): bool
    {
        return isset($this->closure);
    }

    public function getClosure(): Closure
    {
        if (is_null($this->closure)) {
            throw new DefinitionException("Você está forçando pegar uma função (Closure) que você não definiu.");
        }
        return $this->closure;
    } 

    public function getClass(): string
    {   
        if(is_null($this->class)) {
            throw new DefinitionException("Você está forçando pegar uma classe que você não definiu.");
        }

        return $this->class;
    }

    public function hasContextual(string $abstract): bool
    {
        return isset($this->contextual[$abstract]);
    }

    public function getContextual(string $abstract): Closure|string|array
    {
        return $this->contextual[$abstract];
    }

    public function when(string $needs, Closure|string|array $give): self
    {
        $this->contextual[$needs] = $give;
        return $this;
    }
}