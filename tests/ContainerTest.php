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

// TESTES DE ID e ID ALTERNATIVO
test('Deve retornar se a string passada é um id alternativo (alias) ou não', function() {
    $c = new Container;

    $c->alias('id', 'id_alternativo_existente');

    expect($c->isAlias('id_alternativo_existente'))->toBeTrue();
    expect($c->isAlias('id_alternativo_inexistente'))->toBeFalse();
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

test('Deve retornar o id final do serviço dentro do container, passando o id final ou um alternativo', function () {
    $c = new Container;

    $c->alias('id', 'id_alternativo');

    expect($c->getIdInContainer('id_alternativo'))->toBe('id');
    expect($c->getIdInContainer('id'))->toBe('id');
});

// TESTES DE INSTÂNCIAS
test('Deve setar uma instância qualquer', function() {
    $c = new Container;

    $c->instance('id', 'qualquer_coisa');

    expect($c->hasInstance('id'))->toBeTrue();
});

test('Deve retornar uma instância pelo id', function () {
    $c = new Container;

    $c->instance('id', 'qualquer_coisa');

    expect($c->getInstance('id'))->toBe('qualquer_coisa');
});

test('Deve retornar todas instâncias existentes', function () {
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

// TESTES PARA SINGLETON
test('Deve setar um singleton no container', function () {
    $c = new Container;

    $c->singleton('id', 'classe_concreta_ou_função');

    expect($c->hasDefinition('id'))->tobeTrue();
    expect($c->getDefinition('id')->isShared())->tobeTrue();
});

test('Deve retornar a exata mesma instância de um singleton setado no container', function () {
    $c = new Container;

    $c->singleton(ClassInterface::class , ClassConcrete::class);

    $instance1 = $c->get(ClassInterface::class);
    $instance2 = $c->get(ClassInterface::class);

    expect(spl_object_id($instance1))->toEqual(spl_object_id($instance2));
});

// TESTES USANDO O SET()
test('Deve setar um serviço em que a parte concreta é uma função (Closure)', function() {
    $c = new Container;

    $c->set('id', function($text) { 
        return $text;
    });

    expect($c->hasDefinition('id'))->toBeTrue();
    expect($c->getDefinition('id')->hasClosure())->toBeTrue();
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
    expect($c->getDefinition('id')->getClass())->toBe(ClassConcrete::class);    
});

test('Deve retornar instâncias diferentes se um serviço for setado pelo método set() no container', function () {
    $c = new Container;

    $c->set(ClassInterface::class , ClassConcrete::class);

    expect($c->get(ClassInterface::class))->not()->toBe($c->get(ClassInterface::class));
    expect(spl_object_id($c->get(ClassInterface::class)))->not()->toEqual(spl_object_id($c->get(ClassInterface::class)));
});

// TESTES USANDO O GET()
test('Deve construir um serviço com o próprio id se a classe concreta não foi passada ao setar o serviço', function () {
    $c = new Container;

    $c->set(ClassConcrete::class);

    expect($c->get(ClassConcrete::class))->toBeInstanceOf(ClassConcrete::class);
});

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

test('Deve construir uma classe concreta que foi setada no container', function () {
    $c = new Container;

    $c->set('id', ClassConcrete::class);
    expect($c->get('id'))->toBeInstanceOf(ClassConcrete::class);    
});

test('Deve construir uma classe mesmo não sendo setada no container desde que as suas dependências possam ser resolvidas pelo container', function() {
    $c = new Container;

    expect($c->get(ClassConcrete::class))->toBeInstanceOf(ClassConcrete::class);
});

test('Deve construir uma classe em que uma dependência é outra classe já setada no container', function() {
    $c = new Container;

    // A precisa de B e também de C
    $c->set(ClassAInterface::class, ClassA::class);
    // B precisa de C
    $c->set(ClassBInterface::class, ClassB::class);
    // C não precisa de ninguém
    $c->set(ClassCInterface::class, ClassC::class);

    expect($c->get(ClassAInterface::class))->toBeInstanceOf(ClassA::class);
});

test('Deve construir uma classe em que as dependências primitivas são passadas no momento do get()', function() {
    $c = new Container;

    // precisa de um parâmetro 'primitiveParameter' (string)
    $c->set('id', ClassNeedPrimitiveParameter::class);

    expect($c->get('id', [
        'primitiveParameter' => 'recebeu o parâmetro' ,
    ]))->toBeInstanceOf(ClassNeedPrimitiveParameter::class);
    expect($c->get('id', [
        'primitiveParameter' => 'recebeu o parâmetro',
    ])->returnParameter())->tobe('recebeu o parâmetro');
});

test('Deve construir uma classe em que as dependências são passadas no momento do get()', function () {
    $c = new Container;

    // precisa de um parâmetro 'classParameter' (ClassParameterInterface)
    $c->set('id', ClassNeedClassParameter::class);
    $c->set(ClassParameterInterface::class, ClassParameter::class);

    // sem passar no get ele pegará do container que no caso é (ClassParameterInterface)
    expect($c->get('id')->getClassParameter())->toBeInstanceOf(ClassParameter::class);

    // mas ao passar no get ele usará o que foi passado que no caso é (OtherClassParameter)
    expect($c->get('id', [
        'classParameter' => new OtherClassParameter,
    ])->getClassParameter())->toBeInstanceOf(OtherClassParameter::class);
});

test('Deve construir uma classe em que uma dependência é um parâmetro primitivo com valor dafault', function () {
    $c = new Container;

    // precisa de um parâmetro 'string' (string) mas por default tem => 'string'
    $c->set('id', CLassWithPrimitiveDefaultParamater::class);

    expect($c->get('id'))->toBeInstanceOf(CLassWithPrimitiveDefaultParamater::class);
});

test('Deve construir uma classe em que uma dependência é "parent"', function () {
    $c = new Container;

    // extende de ParentOfAClass e precisa de uma dependência do tipo parent ou seja (ParentOfAClass)
    $c->set('id', ClassWithParentAndParentDependency::class);

    expect($c->get('id'))->toBeInstanceOf(ClassWithParentAndParentDependency::class);
});

test('Deve construir uma classe mesmo sem ter os parâmetros, desde que eles sejam opcionais', function () {
    $c = new Container;

    class AnClass {
    public function __construct(private string $parameter = 'default') {
        }
    }

    $c->set('id', AnClass::class);
    expect($c->get('id'))->toBeInstanceOf(AnClass::class);
});

// variadic primitive
test('Deve construir uma classe em que uma dependência é um parâmetro variadic primitivo, e é passado pelo get() como array', function () {
    $c = new Container;

    // precisa de um parâmetro variadic '...string' (string)
    $c->set('id', ClassWithPrimitiveVariadicParameter::class);

    expect($c->get('id', ['string' => ['maozin', 'paozin']]))->toBeInstanceOf(ClassWithPrimitiveVariadicParameter::class);        
});

test('Deve construir uma classe em que uma dependência é um parâmetro variadic primitivo, e é passado pelo get() sem ser array', function () {
    $c = new Container;

    // precisa de um parâmetro variadic '...string' (string)
    $c->set('id', ClassWithPrimitiveVariadicParameter::class);

    expect($c->get('id', ['string' => 'maozin']))->toBeInstanceOf(ClassWithPrimitiveVariadicParameter::class);

});

// variadic class
test('Deve construir uma classe em que uma dependência é um parâmetro variadic de classe', function () {
    $c = new Container;

    $c->set('id', ClassWithClassVariadicParameter::class);

    expect($c->get('id'))->toBeInstanceOf(ClassWithClassVariadicParameter::class);
});

test('Deve construir uma classe em que uma dependência é um parâmetro variadic de classe, e é passado pelo get() como array', function () {
    $c = new Container;

    // precisa de um parâmetro '...$classes (ClassC)
    $c->set('id', ClassWithClassVariadicParameter::class);

    expect($c->get('id', [
        'classes' => [
            new ClassC, new ClassC
        ]
    ]))->toBeInstanceOf(ClassWithClassVariadicParameter::class); 
});

test('Deve construir uma classe em que uma dependência é um parâmetro variadic de classe, e é passado pelo get() sem ser array', function () {
    $c = new Container;

    // precisa de um parâmetro '...$classes (ClassC)
    $c->set('id', ClassWithClassVariadicParameter::class);

    expect($c->get('id', [
        'classes' => new ClassC
    ]))->toBeInstanceOf(ClassWithClassVariadicParameter::class);

});

// CONTEXTO
test('Deve construir um serviço que tem contexto', function () {
    $c = new Container;

    // em vez de usar uma clase setada no container para 'ClassInterface::class' vai ser usada a classe 'OtherClassImplementInterface'
    $c->set('id', ClassNeedOtherClass::class)->when(ClassInterface::class , OtherClassImplementInterface::class);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedOtherClass::class);
    expect($c->get('id')->getImplementedClass())->toBeInstanceOf(OtherClassImplementInterface::class);
});

test('Deve construir um serviço que tem contexto dando prioridade aos parâmetros passados no get()', function () {
    $c = new Container;

    $c->set('id', ClassNeedOtherClass::class)->when(ClassInterface::class , OtherClassImplementInterface::class);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedOtherClass::class);
    expect($c->get('id', ['anClass' => new ClassImplementInterface])->getImplementedClass())->toBeInstanceOf(ClassImplementInterface::class);
});

test('Deve construir um serviço que tem contexto para um parâmetro variadic, passando um array de classes para construir', function () {
    $c = new Container;

    $c->set('id', ClassNeedVariadicClass::class)->when(ClassInterface::class , [
        ClassImplementInterface::class ,
        OtherClassImplementInterface::class ,
    ]);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedVariadicClass::class);
});

test('Deve construir um serviço que tem contexto para um parâmetro variadic, passando apenas uma classe para construir', function () {
    $c = new Container;

    // precisa de um parâmetro ...$classes (ClassInterface)
    $c->set('id', ClassNeedVariadicClass::class)->when(ClassInterface::class , ClassImplementInterface::class);

    expect($c->get('id'))->toBeInstanceOf(ClassNeedVariadicClass::class);
});

// TESTES DE ERRO 
test('Deve retornar um erro ao tentar resolver um serviço com um id não existente e od id não sendo uma classe existente', function () {
    $c = new Container;

    expect(fn() => $c->get(ClassNonexistent::class))->toThrow(NotFoundException::class);
});

test('Deve retornar um erro ao tentar construir uma classe abstrada', function() {
    $c = new Container;

    $c->set('id', ClassInterface::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});

test('Deve retornar um erro ao tentar construir uma classe que não existe', function () {
    $c = new Container;

    $c->set('id', NonexistentClass::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});

test('Deve retornar um erro ao tentar resolver construir uma classe que precisa de um parâmetro primitivo sem valor default', function () {
    $c = new Container;

    class ClassNotReceivePrimitiveParameter {
    public function __construct(string $needThisParameter) {
        }
    }

    $c->set('id', ClassNotReceivePrimitiveParameter::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});

test('Deve retornar um erro ao tentar construir uma classe que precisa de um parâmetro de classe sem ter valor default', function () {
    $c = new Container;

    $c->set('id', CLassWithoutPrimitiveDefaultParameter::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);        
});

test('Deve retornar um erro ao tentar construir uma classe que depende dela mesma (gerando um loop infinito)', function() {
    $c = new Container;

    $c->set('id', ClassLoopConstructor::class);

    expect(fn() => $c->get('id'))->toThrow(BindingResolutionException::class);
});

// TESTE PARA PARÂMETROS
test('Deve setar, verificar e retornar parâmetros setados no container', function() {
    $c = new Container;

    $c->setParameter('id', 'valor qualquer');

    expect($c->hasParameter('id'))->toBeTrue();
    expect($c->getParameter('id'))->toBe('valor qualquer');
});