<div align="center">

# MINIZORD CONTAINER

Apenas um simples container para injeção de dependências.

Este container é um Zord do Minizord Framework ainda em desenvolvimento.

[Instalação](#Instalação) •
[Guia de uso](#guide)

</div>

## Instalação
<br/>

### *Passo 1: Instale o pacote no seu projeto*
Para instalar no seu projeto use o comando:
```sh
composer require minizord/container
```
<br/>

### *Passo 2: Importe o container*
```php
use Minizord\Container\Container;
```
<br/>

### *Passo 3: Instancie o container*
```php
$container = new Container;
```


<br/>


## Guia de uso

<br/>

### SET

Setando um serviço comum.


```php
# você seta o serviço usando sua interface que será o id do serviço.

$container->set(ClassInterface::class, ClassImplementation::class);


# em outra parte do seu sistema você pega a classe instanciada.

$classeInstanciada = $container->get(ClassInterface::class);
```
<br/>
<br/>

Setando um singleton.


```php

$container->singleton(ClassInterface::class, ClassImplementation::class);


# sempre será a mesma instância.

$classeInstanciada = $container->get(ClassInterface::class);
```
<br/>
<br/>

Setando uma instância diretamente.


```php

$container->instante(ClassInterface::class, new ClassImplementation);


# pega a instancia e sempre será o mesmo objeto.

$classeInstanciada = $container->get(ClassInterface::class);
```
<br/>
<br/>

### ALIAS

Você também pode setar um id alternativo para seu serviço


```php
$container->set(ClassInterface::class, ClassImplementation::class);


# você deve passar primeiro o  que representa o serviço (geralmente sua interface) e depois o id alternativo/apelido para o serviço

$container->alias(ClassInterface::class, 'id_alternativo');


$classeInstanciada = $container->get('id_alternativo');
```
<br/>
<br/>

### GET
É possível passar parâmetros para construir a classe no momento do get()


```php
# suponha que ClassImplemantation tem uma depebdência de 'OtherClassInterface $otherClass'
$container->set(ClassInterface::class, ClassImplementation::class);


# então você pode passsar assim
$classeInstanciada = $container->get(ClassInterface::class, [
  'otherClass' => new OtherClass
]);
```
<br/>
<br/>

### CONTEXTO
Você pode usar contexto para dar a um determinado serviço uma classe diferente da que ele receberia pelo fluxo normal do container

```php

$container->set(ClassInterface::class, ClassImplementation::class);

# caso você fizesse isso ao usar get() para pegar esse serviço seria dado para ele 'ClassImplementation'
$container->set('id', ClassNeedClassInterface::class);

# dessa maneira em vez de receber 'ClassImplementation' o container tenta dar a esta classe 'OtherClassImplementation'
$container->set('id', ClassNeedClassInterface::class)->when(ClassInterface::class, OtherClassImplementation::class);
```
<br/>
<br/>

### PARÂMETROS
É possível setar tipos primitivos como parâmetros para recuperar em outros lugares do sistema, eles NÃO são usados pra construir os serviços

```php
# seta um parâmetro

$container->setParameter('id', 'valor');


# verifica a existencia de determinado parâmetro

$container->hasParameter('id');


# recupera determinado parâmetro

$container->getParameter('id');
```
<br/>
<br/>
