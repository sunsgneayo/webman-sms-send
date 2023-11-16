<?php declare(strict_types=1);

namespace support;

use BadMethodCallException;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connections\PhpRedisConnection;
use support\Redis;

/**
 * 统一管理Redis连接，不使用Redis::select()来切换数据库
 * @method static Connection|PhpRedisConnection list() db4 列表数据
 */
final class RedisScope
{
    private const DB_LIST = [
        'list'            => 4,
    ];

    /**
     * 返回已经选择了特定数据库的连接
     * @param int $dbIndex
     * @return Connection|PhpRedisConnection
     * @throws \RedisException
     */
    private static function _runIn(int $dbIndex): Connection|PhpRedisConnection
    {
        /**
         * @var Connection|PhpRedisConnection $conn
         */
        $conn = Redis::connection();
        if ($conn->getDbNum() !== $dbIndex) {
            $conn->select($dbIndex);
        }
        return $conn;
    }

    public static function __callStatic(string $name, array $arguments): Connection|PhpRedisConnection
    {
        if (!isset(self::DB_LIST[$name])) {
            throw new BadMethodCallException("方法{$name}不存在");
        }
        return self::_runIn(self::DB_LIST[$name]);
    }
}
