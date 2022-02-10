<?php

namespace Minizord\Container\Tests\Fixtures;

use Minizord\Container\Tests\Fixtures\ClassC;

class ClassWithClassVariadicParameter {
    public function __construct(ClassC ...$classes)
    {
    }
}