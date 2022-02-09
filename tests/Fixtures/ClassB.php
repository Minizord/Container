<?php

namespace Minizord\Container\Tests\Fixtures;

use Minizord\Container\Tests\Fixtures\ClassBInterface;
use Minizord\Container\Tests\Fixtures\ClassCInterface;

class ClassB implements ClassBInterface {

    public function __construct(ClassCInterface $classC) {}

}