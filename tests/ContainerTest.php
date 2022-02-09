<?php
use Minizord\Container\Container;
use Minizord\Container\Interfaces\DefinitionInterface;
use Minizord\Container\ClassConcrete;

// TESTES DE ID ALTERNATIVO
test('Deve retornar se a string passada é um id alternativo (alias) ou não', function() {
    $c = new Container;

    $c->alias('id', 'id_alternativo_existente');

    expect($c->isAlias('id_alternativo_existente'))->toBeTrue();
    expect($c->isAlias('id_alternativo_inexistente'))->toBeFalse();
});

test('Deve setar um id alternativo (alias)', function() {
    $c = new Container;

    $c->alias('id', 'id_alternativo');

    expect($c->hasAlias('id_alternativo'))->toBeTrue();
});

test('Deve retornar todos os id alternativos (alias) registrados', function() {
    $c = new Container;

    $c->alias('id', 'id_alternativo');
    $c->alias('id', 'outro_id_alternativo');

    expect($c->getAliases())->toEqual([
        'id_alternativo' => 'id',
        'outro_id_alternativo' => 'id',
    ]);
});

test('Deve retornar todos os id alternativos (alias) de um serviço pelo id', function() {
    $c = new Container;

    $c->alias('id', 'id_alternativo');
    $c->alias('id', 'outro_id_alternativo');
    $c->alias('id2', 'id_alternativo_');
    $c->alias('id2', 'outro_id_alternativo_2');

    expect($c->getAliasesById('id'))->toEqual([
        'id_alternativo',
        'outro_id_alternativo'
    ]);

});

// TESTES DE INSTANCIAS
test('Deve setar uma instancia qualquer', function() {
    $c = new Container;

    $c->instance('id', 'qualquer_coisa');

    expect($c->hasInstance('id'))->toBeTrue();
});

test('Deve retornar uma instancia pelo id', function () {
    $c = new Container;

    $c->instance('id', 'qualquer_coisa');

    expect($c->getInstance('id'))->toBe('qualquer_coisa');
});

test('Deve retornar todas instancias existentes', function () {
    $c = new Container;

    $c->instance('id', 'qualquer_coisa');

    expect($c->getInstances())->toEqual([
        'id' => 'qualquer_coisa'
    ]);
});

// TESTES DE DEFINIÇÕES
test('Deve setar um serviço com um id criando assim uma definição de serviço no container', function() {
    $c = new Container;

    $c->set('id', 'classe_concreta_ou_função');

    expect($c->hasDefinition('id'))->toBeTrue();
});

test('Deve retornar uma definição de um serviço pelo id', function() {
    $c = new Container;

    $c->set('id', 'classe_concreta_ou_função');

    expect($c->getDefinition('id'))->toBeInstanceOf(DefinitionInterface::class);
});

test('Deve retornar todas as definições', function () {
    $c = new Container;

    $c->singleton('id', 'classe_concreta_ou_função');
    $c->singleton('id2', 'classe_concreta_ou_função_2');

    expect($c->getDefinitions())->toEqual([
        'id' => $c->getDefinition('id'),
        'id2' => $c->getDefinition('id2'),
    ]);
});

// TESTES DE SINGLETON
test('Deve setar um singleton no container', function() {
    $c = new Container;

    $c->singleton('id', 'classe_concreta_ou_função');

    expect($c->hasDefinition('id'))->tobeTrue();
    expect($c->getDefinition('id')->isShared())->tobeTrue();
});

// TESTES DOS MÉTODOS PRINCIPAIS
test('Deve retornar se um determinado serviço existe no container passando um id ou id alternativo', function() {
    $c = new Container;

    $c->singleton('id', 'classe_concreta_ou_função');
    $c->singleton('id2', 'classe_concreta_ou_função_2');

    expect($c->has('id'))->toBeTrue();
});

test('Deve setar um serviço em que a parte concreta é uma função (Closure)', function() {
    $c = new Container;

    $c->set('id', function($text) { 
        return $text;
    });

    expect($c->hasDefinition('id'))->toBeTrue();
});

test('Deve setar um serviço em que a parte concreta é null', function () {
    $c = new Container;

    $c->set('id');

    expect($c->hasDefinition('id'))->toBeTrue();    
});

test('Deve setar um serviço em que a parte concreta é uma classe', function () {
    $c = new Container;

    $c->set('id', ClassConcrete::class);

    expect($c->hasDefinition('id'))->toBeTrue();    
});

// TESTE DE MÉTODO MAIS GERAIS
test('Deve retornar o id final do serviço dentro do container, passando o id final ou um alternativo', function() {
    $c = new Container;

    $c->alias('id', 'id_alternativo');

    expect($c->getIdInContainer('id_alternativo'))->toBe('id');
    expect($c->getIdInContainer('id'))->toBe('id');

    var_dump($c);
});

