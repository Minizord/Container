<?php

namespace Minizord\Container\Tests\Fixtures;

use Minizord\Container\Tests\Fixtures\ClassParameterInterface;

class ClassNeedClassParameter {

    public function __construct(private ClassParameterInterface $classParameter)
    {

    }

    public function getClassParameter()
    {
        return $this->classParameter;
    }

}