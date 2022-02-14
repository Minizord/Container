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

### *Passo 2: Importe o container*
```php
use Minizord\Container\Container;
```

### *Passo 3: Instancie o container*
```php
$container = new Container;
```


<br/>

## Guia de uso

<br/>
<br/>

O container é feito para que você passe instruções e sete classes e funções no container para depois pega-los em outra parte do seu sistema.

```php
# você seta o serviço usando sua interface que será o id do serviço.

$container->set(ClassInterface::class, ClassImplementation::class);


# em outra parte do seu sistema você pega a classe instanciada.

$classeInstanciada = $container->get(ClassInterface::class);
```
<br/>
<br/>

Você também pode setar um id alternativo para seu serviço

```php
$container->set(ClassInterface::class, ClassImplementation::class);


# você deve passar primeiro o  que representa o serviço (geralmente sua interface) e depois o id alternativo/apelido para o serviço

$container->alias(ClassInterface::class, 'id_alternativo')


$classeInstanciada = $container->get('id_alternativo');
```
