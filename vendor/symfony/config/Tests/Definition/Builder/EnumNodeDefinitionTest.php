<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests\Definition\Builder;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
class EnumNodeDefinitionTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testWithOneValue()
    {
        $def = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition('foo');
        $def->values(['foo']);
        $node = $def->getNode();
        $this->assertEquals(['foo'], $node->getValues());
    }
    public function testWithOneDistinctValue()
    {
        $def = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition('foo');
        $def->values(['foo', 'foo']);
        $node = $def->getNode();
        $this->assertEquals(['foo'], $node->getValues());
    }
    public function testNoValuesPassed()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('You must call ->values() on enum nodes.');
        $def = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition('foo');
        $def->getNode();
    }
    public function testWithNoValues()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('->values() must be called with at least one value.');
        $def = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition('foo');
        $def->values([]);
    }
    public function testGetNode()
    {
        $def = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition('foo');
        $def->values(['foo', 'bar']);
        $node = $def->getNode();
        $this->assertEquals(['foo', 'bar'], $node->getValues());
    }
    public function testSetDeprecated()
    {
        $def = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Definition\Builder\EnumNodeDefinition('foo');
        $def->values(['foo', 'bar']);
        $def->setDeprecated('The "%path%" node is deprecated.');
        $node = $def->getNode();
        $this->assertTrue($node->isDeprecated());
        $this->assertSame('The "foo" node is deprecated.', $def->getNode()->getDeprecationMessage($node->getName(), $node->getPath()));
    }
}
