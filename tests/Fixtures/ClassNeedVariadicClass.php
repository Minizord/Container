<?php
namespace Minizord\Container\Tests\Fixtures;

use Minizord\Container\Tests\Fixtures\ClassInterface;

class ClassNeedVariadicClass {
    public function __construct(ClassInterface ...$classes)
    {

    }
}