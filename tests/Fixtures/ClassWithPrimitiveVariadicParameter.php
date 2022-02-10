<?php

namespace Minizord\Container\Tests\Fixtures;

class ClassWithPrimitiveVariadicParameter {
    public function __construct(string ...$string)
    {
    }
}