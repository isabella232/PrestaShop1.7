<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\CheckReferenceValidityPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
class CheckReferenceValidityPassTest extends TestCase
{
    public function testProcessDetectsReferenceToAbstractDefinition()
    {
        $this->expectException('RuntimeException');
        $container = new ContainerBuilder();
        $container->register('a')->setAbstract(true);
        $container->register('b')->addArgument(new Reference('a'));
        $this->process($container);
    }
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('a')->addArgument(new Reference('b'));
        $container->register('b');
        $this->process($container);
        $this->addToAssertionCount(1);
    }
    protected function process(ContainerBuilder $container)
    {
        $pass = new CheckReferenceValidityPass();
        $pass->process($container);
    }
}
