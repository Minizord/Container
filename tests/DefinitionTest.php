<?php

use Minizord\Container\Definition;

test('Deve retornar o id da definição', function () {

    $d = new Definition('definition.id', 'definition.class');
    expect($d->getId())->toBe('definition.id');
});

test('Deve retornar se a definição é compartilhada ou seja se é um singleton', function () {

    $d = new Definition('definition.id', 'definition.class', null, true);
    expect($d->isShared())->toBeTrue();

    $d = new Definition('definition.id', 'definition.class', null, false);
    expect($d->isShared())->toBeFalse();
});

test('Deve setar se a definição é compartilhada ou seja definir se é singleton ou não', function () {

    $d = new Definition('definition.id', 'definition.class');

    $d->setShared(true);
    expect($d->isShared())->toBeTrue();
    
    $d->setShared(false);
    expect($d->isShared())->toBeFalse();
});

test('Deve retornar se a definição tem uma closure ou não', function () {

    $d = new Definition('definition.id', null, function(){});
    expect($d->hasClosure())->toBeTrue();
    
    $d = new Definition('definition.id', 'definition.class', null);
    expect($d->hasClosure())->toBeFalse();
});