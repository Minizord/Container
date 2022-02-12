<?php

namespace Minizord\Container\Tests\Fixtures;

use Minizord\Container\Tests\Fixtures\ClassInterface;

class ClassNeedOtherClass
{
    public function __construct(private ClassInterface $anClass)
    {
    }
    public function getImplementedClass()
    {
        return $this->anClass;
    }
}