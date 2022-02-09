<?php

namespace Minizord\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Minizord\Container\Exceptions\ContainerException;

class NotFoundException extends Exception implements ContainerExceptionInterface {}