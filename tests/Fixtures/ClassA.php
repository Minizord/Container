<?php

namespace Minizord\Container\Tests\Fixtures;

use Minizord\Container\Tests\Fixtures\ClassBInterface;
use Minizord\Container\Tests\Fixtures\ClassCInterface;

class ClassA implements ClassAInterface {


    public function __construct(ClassBInterface $classB, ClassCInterface  $classC){}

}