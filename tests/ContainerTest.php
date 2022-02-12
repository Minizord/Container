<?php
use Minizord\Container\Container;
use Minizord\Container\Interfaces\DefinitionInterface;

use Minizord\Container\Exceptions\NotFoundException;
use Minizord\Container\Exceptions\BindingResolutionException;

use Minizord\Container\Tests\Fixtures\ClassAInterface;
use Minizord\Container\Tests\Fixtures\ClassA;

use Minizord\Container\Tests\Fixtures\ClassBInterface;
use Minizord\Container\Tests\Fixtures\ClassB;

use Minizord\Container\Tests\Fixtures\ClassCInterface;
use Minizord\Container\Tests\Fixtures\ClassC;

use Minizord\Container\Tests\Fixtures\ClassInterface;
use Minizord\Container\Tests\Fixtures\ClassConcrete;
use Minizord\Container\Tests\Fixtures\ClassImplementInterface;
use Minizord\Container\Tests\Fixtures\OtherClassImplementInterface;

use Minizord\Container\Tests\Fixtures\ClassParameterInterface;
use Minizord\Container\Tests\Fixtures\ClassParameter;
use Minizord\Container\Tests\Fixtures\OtherClassParameter;

use Minizord\Container\Tests\Fixtures\ClassNeedOtherClass;
use Minizord\Container\Tests\Fixtures\ClassLoopConstructor;
use Minizord\Container\Tests\Fixtures\ClassNeedClassParameter;
use Minizord\Container\Tests\Fixtures\ClassNeedPrimitiveParameter;
use Minizord\Container\Tests\Fixtures\ClassWithClassVariadicParameter;
use Minizord\Container\Tests\Fixtures\ClassWithParentAndParentDependency;
use Minizord\Container\Tests\Fixtures\CLassWithPrimitiveDefaultParamater;
use Minizord\Container\Tests\Fixtures\ClassWithPrimitiveVariadicParameter;
use Minizord\Container\Tests\Fixtures\ClassNeedVariadicClass;

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

// TESTES DOS MÉTODOS PRINCIPAIS

// set
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

//get
test('Deve retornar a execução de uma função (Closure) que foi setada no container como serviço', function() {
    $c = new Container;

    $c->set('id', function() {
        return 'alguma_coisa';
    });

    $c->set('id2', function () {
        return new ClassConcrete;
    });

    expect($c->get('id'))->toBe('alguma_coisa');
    expect($c->get('id2'))->toBeInstanceOf(ClassConcrete::class);
});

test('Deve retornar uma instancia da classe concreta que foi setada como servço no container', function () {
    $c = new Container;

    $c->set('id', ClassConcrete::class);
    expect($c->get('id'))->toBeInstanceOf(ClassConcrete::class);    
});

test('Deve retornar uma instancia do próprio id se a classe concreta não foi passada ao setar o serviço', function () {
    $c = new Container;

    $c->set(ClassConcrete::class);

    expect($c->get(ClassConcrete::class))->toBeInstanceOf(ClassConcrete::class);
});

test('Deve retornar uma classe q não foi setada se tiver como resolver', function() {
    $c = new Container;

    expect($c->get(ClassConcrete::class))->toBeInstanceOf(ClassConcrete::class);
});

test('Deve retornar uma instancia de um serviço que depende de outras classes setadas no container', function() {
    $c = new Container;

    // A precisa de B e também de C
    $c->set(ClassAInterface::class, ClassA::class);
    // B precisa de C
    $c->set(ClassBInterface::class, ClassB::class);
    // C não precisa de ninguém
    $c->set(ClassCInterface::class, ClassC::class);

    expect($c->get(ClassAInterface::class))->toBeInstanceOf(ClassA::class);
});

test('Deve retornar uma instancia montada com os parametros primitivos passados na hora do get()', function() {
    $c = new Container;

    $c->set('id', ClassNeedPrimitiveParameter::class);

    expect($c->get('id', [
        'primitiveParameter' => 'recebeu o parametro' ,
    ]))->toBeInstanceOf(ClassNeedPrimitiveParameter::class);
    expect($c->get('id', [
        'primitiveParameter' => 'recebeu o parametro',
    ])->returnParameter())->tobe('recebeu o parametro');
});

test('Deve retornar uma instancia montada com os parametros de classe passados na hora do get()', function () {
    $c = new Container;

    $c->set('id', ClassNeedClassParameter::class);
    $c->set(ClassParameterInterface::class, ClassParameter::class);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedClassParameter::class);
    expect($c->get('id')->getClassParameter())->toBeInstanceOf(ClassParameter::class);
    expect($c->get('id', [
        'classParameter' => new OtherClassParameter,
    ])->getClassParameter())->toBeInstanceOf(OtherClassParameter::class);
});

test('Deve retornar uma instancia em que uma dependencia é um parametro variadic primitivo ', function () {
    $c = new Container;

    $c->set('id', ClassWithPrimitiveVariadicParameter::class);

    expect($c->get('id', ['string' => ['maozin', 'paozin']]))->toBeInstanceOf(ClassWithPrimitiveVariadicParameter::class);        
});

test('Deve retornar uma instancia em que uma dependencia é um parametro variadic de classe, e é passado pelo get()', function () {
    $c = new Container;

    // => ClassC ...$classes
    $c->set('id', ClassWithClassVariadicParameter::class);

    expect($c->get('id', [
        'classes' => [
            new ClassC, new ClassC
        ]
    ]))->toBeInstanceOf(ClassWithClassVariadicParameter::class); 
});

test('Deve retornar uma instancia em que uma dependencia é um parametro variadic de classe, e é passado pelo get() sem ser array', function () {
    $c = new Container;

    // => ClassC ...$classes
    $c->set('id', ClassWithClassVariadicParameter::class);

    expect($c->get('id', [
        'classes' => new ClassC
    ]))->toBeInstanceOf(ClassWithClassVariadicParameter::class);

});

test('Deve retornar uma instancia em que uma dependencia é um parametro variadic de classe', function () {
    $c = new Container;

    $c->set('id', ClassWithClassVariadicParameter::class);

    expect($c->get('id'))->toBeInstanceOf(ClassWithClassVariadicParameter::class);
});

test('Deve construir uma classe em que uma dependencia é "parent"', function() {
    $c = new Container;

    $c->set('id', ClassWithParentAndParentDependency::class);

    expect($c->get('id'))->toBeInstanceOf(ClassWithParentAndParentDependency::class);
});



test('Deve retornar um erro ao tentar construir uma classe q precisa de um parametro primitivo sem valor default e sem with()', function () {
    $c = new Container;

    class ClassNotReceivePrimitiveParameter {
    public function __construct(string $needThisParameter) {
        }
    }

    $c->set('id', ClassNotReceivePrimitiveParameter::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);        
});

test('Deve construir a classe mesmo sem ter os parametros, desde que eles sejam opcionais', function () {
    $c = new Container;

    class AnClass {
    public function __construct(private string $parameter = 'default') {
        }
    }

    $c->set('id', AnClass::class);
    expect($c->get('id'))->toBeInstanceOf(AnClass::class);        
});

// CONTEXTO
test('Deve construir um serviço que tem contexto', function () {
    $c = new Container;

    $c->set('id', ClassNeedOtherClass::class)->when(ClassInterface::class , OtherClassImplementInterface::class);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedOtherClass::class);
    expect($c->get('id')->getImplementedClass())->toBeInstanceOf(OtherClassImplementInterface::class);
});

test('Deve construir um serviço que tem contexto, dando prioridade aos parametros passados para contruir', function () {
    $c = new Container;

    $c->set('id', ClassNeedOtherClass::class)->when(ClassInterface::class , OtherClassImplementInterface::class);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedOtherClass::class);
    expect($c->get('id', ['anClass' => new ClassImplementInterface])->getImplementedClass())->toBeInstanceOf(ClassImplementInterface::class);
});

test('Deve construir um serviço que tem contexto para um parametro variadic', function () {
    $c = new Container;

    $c->set('id', ClassNeedVariadicClass::class)->when(ClassInterface::class , [
        ClassImplementInterface::class ,
        OtherClassImplementInterface::class ,
    ]);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedVariadicClass::class);
});

test('Deve construir um serviço que tem contexto para um parametro variadic, passando apenas a classe', function () {
    $c = new Container;

    $c->set('id', ClassNeedVariadicClass::class)->when(ClassInterface::class , ClassImplementInterface::class);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedVariadicClass::class);
});

// singleton
test('Deve setar um singleton no container', function () {
    $c = new Container;

    $c->singleton('id', 'classe_concreta_ou_função');

    expect($c->hasDefinition('id'))->tobeTrue();
    expect($c->getDefinition('id')->isShared())->tobeTrue();
});

test('Deve retornar a exata mesma instancia de um singleton setado no container', function () {
    $c = new Container;

    $c->singleton(ClassInterface::class, ClassConcrete::class);

    expect($c->get(ClassInterface::class))->toBe($c->get(ClassInterface::class));
    expect(spl_object_id($c->get(ClassInterface::class)))->toEqual(spl_object_id($c->get(ClassInterface::class)));
});

test('Deve retornar instancias diferentes se um serviço for setado normalmente no container', function () {
    $c = new Container;

    $c->set(ClassInterface::class, ClassConcrete::class);

    expect($c->get(ClassInterface::class))->not()->toBe($c->get(ClassInterface::class));
    expect(spl_object_id($c->get(ClassInterface::class)))->not()->toEqual(spl_object_id($c->get(ClassInterface::class)));
});

test('Deve retornar uma instancia em que a classe precisa de um parametro primitivo com valor dafualt', function () {
    $c = new Container;

    $c->set('id', CLassWithPrimitiveDefaultParamater::class);

    expect($c->get('id'))->toBeInstanceOf(CLassWithPrimitiveDefaultParamater::class);
});

// TESTES DE ERRO 
test('Deve retornar um erro ao buscar com um id não existente e sendo uma classe não existente', function () {
    $c = new Container;

    expect(fn() => $c->get(ClassNonexistent::class))->toThrow(NotFoundException::class);
});

test('Deve retornar um erro ao tentar resolver um serviço que foi passado uma classe abstrada ao invés de concreta', function() {
    $c = new Container;

    $c->set('id', ClassInterface::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});

test('Deve retornar um erro ao tentar resolver uma de um serviço', function () {
    $c = new Container;

    $c->set('id', ClassA::class);
    $c->set(ClassBInterface::Class, ClassBInterface::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});

test('Deve retornar um erro ao tentar resolver uma de um serviço em que a classe concreta não existe', function () {
    $c = new Container;

    $c->set('id', Nonexistent::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});

test('Deve retornar um erro ao tentar resolver uma classe que precisa de um parametro primitivo sem ter valor default', function () {
    $c = new Container;

    $c->set('id', CLassWithoutPrimitiveDefaultParameter::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);        
});

test('Deve retornar um erro ao tentar construir uma classe que depende dela mesma (gerando um loop infinito)', function() {
    $c = new Container;

    $c->set('id', ClassLoopConstructor::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});



// OUTROS
test('Deve retornar o id final do serviço dentro do container, passando o id final ou um alternativo', function () {
    $c = new Container;

    $c->alias('id', 'id_alternativo');

    expect($c->getIdInContainer('id_alternativo'))->toBe('id');
    expect($c->getIdInContainer('id'))->toBe('id');
});


// PARAMETROS
test('Deve setar, verificar e pegar parametros', function() {
    $c = new Container;

    $c->setParameter('id', 'valor qualquer');

    expect($c->hasParameter('id'))->toBeTrue();
    expect($c->getParameter('id'))->toBe('valor qualquer');
});