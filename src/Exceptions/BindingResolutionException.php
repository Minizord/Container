<?php

namespace Minizord\Container\Exceptions;

use TheSeer\Tokenizer\Exception;
use Minizord\Container\Exceptions\ContainerExceptionInterface;

class BindingResolutionException extends Exception implements ContainerExceptionInterface {}