<?php

namespace Minizord\Container\Interfaces;

interface DefinitionInterface {

    /**
     * Retorna o próprio ID
     *
     * @return string  ID do serviço
     */
    public function getId(): string;

    /**
     * Retorna se o serviço é um singleton ou não
     *
     * @return boolean
     */
    public function isShared(): bool;

    /**
     * Tranforma esse serviço em um singleton ou não
     *
     * @param boolean  $shared  Determina se será um singleton
     * @return void
     */
    public function setShared(bool $shared): void;

    /**
     * Retorna se esse servio é uma função (Closure)
     *
     * @return boolean
     */
    public function hasClosure(): bool;
}