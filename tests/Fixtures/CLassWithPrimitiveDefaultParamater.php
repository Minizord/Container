<?php

namespace Minizord\Container\Tests\Fixtures;

class CLassWithPrimitiveDefaultParamater {
    public function __construct(string $string = 'string')
    {
    }
}