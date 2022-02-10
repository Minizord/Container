<?php

namespace Minizord\Container\Tests\Fixtures;

class ClassLoopConstructor {

    public function __construct(self $me)
    {
    }

}