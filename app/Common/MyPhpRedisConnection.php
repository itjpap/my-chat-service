<?php


namespace App\Common;

use Swoft\Log\Helper\Log;
use Swoft\Redis\Connection\Connection;
use Swoft\Redis\Exception\RedisException;
use Swoft\Redis\Redis;
use Swoft\Redis\RedisEvent;

/**
 * Class MyPhpRedisConnection
 * @package App\Common
 * Created by lujianjin
 * DataTime: 2020/10/14 15:22
 */
class MyPhpRedisConnection extends Connection
{

    /**
     * @var Redis | \RedisCluster
     */
    public $client;


    /**
     * Notes:
     *
     * @author: lujianjin
     * datetime: 2020/10/14 16:04
     * @param string $key
     * @param int $iterator 第一次一定要传
     * @param string|null $pattern
     * @param int $count
     * @return array|bool|false
     */
    public function sScan(string $key, int &$iterator, string $pattern = null, int $count = 0)
    {
        return $this->myCommand('sScan', $key, $iterator, $pattern, $count);
    }

    /**
     * Notes:
     *
     * @param int $iterator 第一次一定要传
     * 0 表示最后
     * null 表示最前
     * @param string|null $pattern
     * @param int $count
     * @return array|bool|void
     * @author: lujianjin
     * datetime: 2020/10/14 16:02
     */
    public function scan(int &$iterator, string $pattern = null, int $count = 0)
    {
        return $this->myCommand('scan', '', $iterator, $pattern, $count);
    }

    /**
     * Notes:
     *
     * @author: lujianjin
     * datetime: 2020/10/14 16:07
     * @param string $key
     * @param int $iterator
     * @param string|null $pattern
     * @param int $count
     * @return array|void
     */
    public function hScan(string $key, int &$iterator, string $pattern = null, int $count = 0)
    {
        return $this->myCommand('hScan', $key, $iterator, $pattern, $count);
    }

    /**
     * Notes:
     *
     * @author: lujianjin
     * datetime: 2020/10/14 16:07
     * @param string $key
     * @param int $iterator
     * @param string|null $pattern
     * @param int $count
     * @return array|void
     */
    public function zScan(string $key, int &$iterator, string $pattern = null, int $count = 0)
    {
        return $this->myCommand('zScan', $key, $iterator, $pattern, $count);
    }


    /**
     * Notes: 重写命令
     *
     * @author: lujianjin
     * datetime: 2020/10/14 15:26
     */
    private function myCommand(string $method, string $key, &$iterator, string $pattern = null, int $count = 0)
    {
        $parameters = [$key, $iterator, $pattern, $count];

        try {
            $lowerMethod = strtolower($method);
            if (!in_array($lowerMethod, \RedisSupportedMethodsEnum::$supportedMethods, true)) {
               throw new RedisException(
                   sprintf('Method(%s) is not supported!', $method)
               );
            }

            // Before event
            \Swoft::trigger(RedisEvent::BEFORE_COMMAND, null, $method, $parameters);

            Log::profileStart('redis.%s', $method);

            // 直接调用
            if ($method === 'scan') {
                $result = $this->client->scan($iterator, $pattern, $count);
            } else {
                $result = $this->client->{$method}($key, $iterator, $pattern, $count);
            }

            Log::profileEnd('redis.%s', $method);

            // After event
            \Swoft::trigger(RedisEvent::AFTER_COMMAND, null, $method, $parameters, $result);

            // Release Connection
            $this->release();
        } catch (\Throwable $e){
            throw new RedisException(
              sprintf('Redis command reconnect error(%s)', $e->getMessage())
            );
        }

        return $result;
    }

}
