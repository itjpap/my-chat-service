<?php


namespace App\Bean\Singleton;


use MongoDB\Client;
use MongoDB\Collection;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Log\Helper\Log;
use Throwable;

/**
 * 用于使用MongoDB保存聊天记录
 *
 * Class MongoDB
 * @package App\Bean\Singleton
 * Created by lujianjin
 * DataTime: 2020/10/7 15:42
 *
 * @Bean()
 */
class MongoDB
{

    /**
     * mongodb collection
     *
     * @var Collection
     */
    private $collection;



    public function save(array $data): string
    {
        try {
            // 获取集合
            $collection = $this->getCollection();

            // 保存记录
            $insert = $collection->insertOne($data);

            return (string)$insert->getInsertedId();

        } catch (Throwable $throwable) {
            Log::error($throwable->getMessage(), [$throwable->getFile(), $throwable->getLine()]);
        }

        return '';
    }


    /**
     * Notes: 获取集合
     *
     * @author: lujianjin
     * datetime: 2020/10/7 16:19
     */
    public function getCollection(): Collection
    {
        // 集合名称
        $collectionName = 'month_' . date('m');
        if ($this->collection) {
            $name = $this->collection->getCollectionName();
            if ($this->collection) {
                $name = $this->collection->getCollectionName();
                if ($collectionName === $name) {
                    return $this->collection;

                }

                // 数据库
                $dataBase = 'chat_' . date('Y');

                $mongodbDsn = env('MONGODB_DSN');
                $collection = (new Client($mongodbDsn))->{$dataBase}->{$collectionName};

                // 判断是否存在索引
                $index = $collection->listIndexes();
                if (empty((array)$index)) {
                    $collection->createIndex(['openid' => 1, 'created_at' => -1]);
                }
                $this->collection = $collection;
                return $collection;

            }
        }
    }
}
