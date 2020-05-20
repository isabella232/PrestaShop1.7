<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Config\Tests;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator;
use ReflectionObject;
use const DIRECTORY_SEPARATOR;

class FileLocatorTest extends TestCase
{
    /**
     * @dataProvider getIsAbsolutePathTests
     */
    public function testIsAbsolutePath($path)
    {
        $loader = new FileLocator([]);
        $r = new ReflectionObject($loader);
        $m = $r->getMethod('isAbsolutePath');
        $m->setAccessible(true);
        $this->assertTrue($m->invoke($loader, $path), '->isAbsolutePath() returns true for an absolute path');
    }
    public function getIsAbsolutePathTests()
    {
        return [['/foo.xml'], ['c:\\\\foo.xml'], ['c:/foo.xml'], ['\\server\\foo.xml'], ['https://server/foo.xml'], ['phar://server/foo.xml']];
    }
    public function testLocate()
    {
        $loader = new FileLocator(__DIR__ . '/Fixtures');
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'FileLocatorTest.php', $loader->locate('FileLocatorTest.php', __DIR__), '->locate() returns the absolute filename if the file exists in the given path');
        $this->assertEquals(__DIR__ . '/Fixtures' . DIRECTORY_SEPARATOR . 'foo.xml', $loader->locate('foo.xml', __DIR__), '->locate() returns the absolute filename if the file exists in one of the paths given in the constructor');
        $this->assertEquals(__DIR__ . '/Fixtures' . DIRECTORY_SEPARATOR . 'foo.xml', $loader->locate(__DIR__ . '/Fixtures' . DIRECTORY_SEPARATOR . 'foo.xml', __DIR__), '->locate() returns the absolute filename if the file exists in one of the paths given in the constructor');
        $loader = new FileLocator([__DIR__ . '/Fixtures', __DIR__ . '/Fixtures/Again']);
        $this->assertEquals([__DIR__ . '/Fixtures' . DIRECTORY_SEPARATOR . 'foo.xml', __DIR__ . '/Fixtures/Again' . DIRECTORY_SEPARATOR . 'foo.xml'], $loader->locate('foo.xml', __DIR__, false), '->locate() returns an array of absolute filenames');
        $this->assertEquals([__DIR__ . '/Fixtures' . DIRECTORY_SEPARATOR . 'foo.xml', __DIR__ . '/Fixtures/Again' . DIRECTORY_SEPARATOR . 'foo.xml'], $loader->locate('foo.xml', __DIR__ . '/Fixtures', false), '->locate() returns an array of absolute filenames');
        $loader = new FileLocator(__DIR__ . '/Fixtures/Again');
        $this->assertEquals([__DIR__ . '/Fixtures' . DIRECTORY_SEPARATOR . 'foo.xml', __DIR__ . '/Fixtures/Again' . DIRECTORY_SEPARATOR . 'foo.xml'], $loader->locate('foo.xml', __DIR__ . '/Fixtures', false), '->locate() returns an array of absolute filenames');
    }
    public function testLocateThrowsAnExceptionIfTheFileDoesNotExists()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\Config\\Exception\\FileLocatorFileNotFoundException');
        $this->expectExceptionMessage('The file "foobar.xml" does not exist');
        $loader = new FileLocator([__DIR__ . '/Fixtures']);
        $loader->locate('foobar.xml', __DIR__);
    }
    public function testLocateThrowsAnExceptionIfTheFileDoesNotExistsInAbsolutePath()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\Config\\Exception\\FileLocatorFileNotFoundException');
        $loader = new FileLocator([__DIR__ . '/Fixtures']);
        $loader->locate(__DIR__ . '/Fixtures/foobar.xml', __DIR__);
    }
    public function testLocateEmpty()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('An empty file name is not valid to be located.');
        $loader = new FileLocator([__DIR__ . '/Fixtures']);
        $loader->locate(null, __DIR__);
    }
}
