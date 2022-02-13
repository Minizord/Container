<?php
 
// Aqui não tinha muito o que testar pois é um monte de getter e setter,
// mas acho que a criação de uma classe para representar uma definição 
// de um serviço deixa as coisas mais claras.

use Minizord\Container\Definition;
use Minizord\Container\Exceptions\DefinitionException;
use Minizord\Container\Tests\Fixtures\ClassInterface;
use Minizord\Container\Tests\Fixtures\ClassConcrete;


// SETTERS
test('Deve setar se a definição é compartilhada ou seja definir se é singleton ou não', function () {
    $d = new Definition('definition.id', 'definition.class');

    $d->setShared(true);
    expect($d->isShared())->toBeTrue();
    
    $d->setShared(false);
    expect($d->isShared())->toBeFalse();
});

test('Deve setar um contexto ', function() {
    $d = new Definition('definition.id', null);

    $d->when(ClassInterface::class, ClassConcrete::class);

    expect($d->hasContextual(ClassInterface::class))->toBeTrue();
    expect($d->getContextual(ClassInterface::class))->toBe(ClassConcrete::class);
});

// GETTERS
test('Deve retornar o id da definição', function () {
    $d = new Definition('definition.id', 'definition.class');

    expect($d->getId())->toBe('definition.id');
});

test('Deve retornar a classe da definição', function () {
    $d = new Definition('definition.id', 'definition.class');

    expect($d->getClass())->toBe('definition.class');
});

test('Deve retornar a função (Closure) da definição', function () {
    $function = fn() => 'uma frase qualquer';
    $d = new Definition('definition.id', null, $function);

    expect($d->getClosure())->toBe($function);
});

// BOOLS
test('Deve retornar se a definição tem uma closure ou não', function () {
    $d = new Definition('definition.id', null, function(){});
    expect($d->hasClosure())->toBeTrue();
    
    $d = new Definition('definition.id', 'definition.class', null);
    expect($d->hasClosure())->toBeFalse();
});

test('Deve retornar se a definição é compartilhada ou seja se é um singleton', function () {
    $d = new Definition('definition.id', 'definition.class', null, true);
    expect($d->isShared())->toBeTrue();

    $d = new Definition('definition.id', 'definition.class', null, false);
    expect($d->isShared())->toBeFalse();
});

test('Deve retornar de há tal contexto ', function () {
    $d = new Definition('definition.id', null);

    $d->when(ClassInterface::class , ClassConcrete::class);

    expect($d->hasContextual(ClassInterface::class))->toBeTrue();
});

// ERROS
test('Deve retornar um erro ao tentar pegar a função (Closure) sem ter definido nenhuma', function () {
    $d = new Definition('definition.id', null, null);

    expect(fn() => $d->getClosure())->toThrow(DefinitionException::class);
});

test('Deve retornar um erro ao tentar pegar a classe sem ter definido nenhuma', function () {
    $d = new Definition('definition.id', null, null);

    expect(fn() => $d->getClass())->toThrow(DefinitionException::class);
});