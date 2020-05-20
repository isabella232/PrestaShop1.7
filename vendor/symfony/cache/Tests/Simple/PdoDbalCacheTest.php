<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Simple;

use _PhpScoper5ea00cc67502b\Doctrine\DBAL\DriverManager;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Simple\PdoCache;
use _PhpScoper5ea00cc67502b\Symfony\Component\Cache\Tests\Traits\PdoPruneableTrait;
use function extension_loaded;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @group time-sensitive
 */
class PdoDbalCacheTest extends CacheTestCase
{
    use PdoPruneableTrait;
    protected static $dbFile;
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('Extension pdo_sqlite required.');
        }
        self::$dbFile = tempnam(sys_get_temp_dir(), 'sf_sqlite_cache');
        $pool = new PdoCache(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => self::$dbFile]));
        $pool->createTable();
    }
    public static function tearDownAfterClass()
    {
        @unlink(self::$dbFile);
    }
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new PdoCache(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => self::$dbFile]), '', $defaultLifetime);
    }
}
