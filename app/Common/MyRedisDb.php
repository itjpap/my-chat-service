<?php


namespace App\Common;


use ReflectionException;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Redis\Connection\Connection;
use Swoft\Redis\Exception\RedisException;
use Swoft\Redis\RedisDb;

/**
 * Class MyRedisDb
 * @package App\Common
 * Created by lujianjin
 * DataTime: 2020/10/14 16:09
 */
class MyRedisDb extends RedisDb
{

    /**
     * Notes:
     *
     * @author: lujianjin
     * datetime: 2020/10/14 16:14
     * @return Connection
     * @throws RedisException
     */
    public function getConnection(): Connection
    {
        /*
         * 这里是框架设计的错误，使用这个方法避免
         */
        $this->connections[self::PHP_REDIS] = bean(MyPhpRedisConnection::class);
        return parent::getConnection();
    }
}
