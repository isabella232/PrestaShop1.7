<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;
return function (ContainerConfigurator $c) {
    $c->import('basic.php');
    $s = $c->services()->defaults()->public()->private()->autoconfigure()->autowire()->tag('t', ['a' => 'b'])->bind(Foo::class, ref('bar'))->private();
    $s->set(Foo::class)->args([ref('bar')])->public();
    $s->set('bar', Foo::class)->call('setFoo')->autoconfigure(false);
};
