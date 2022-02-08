<?php

namespace Minizord\Container\Interfaces;

interface DefinitionInterface {
    public function getId(): string;
    public function isShared(): bool;
    public function setShared(bool $shared): void;
    public function hasClosure(): bool;
}