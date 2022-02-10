<?php

namespace Minizord\Container\Tests\Fixtures;

use Minizord\Container\Tests\Fixtures\ParentOfAClass;

class ClassWithParentAndParentDependency extends ParentOfAClass {
    public function __construct(parent $parent) {}
}