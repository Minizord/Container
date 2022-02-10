<?php

namespace Minizord\Container\Tests\Fixtures;

class ClassNeedPrimitiveParameter {

    public function __construct(private string $primitiveParameter)
    {
    }

    public function returnParameter()
    {
        return $this->primitiveParameter;
    }

}