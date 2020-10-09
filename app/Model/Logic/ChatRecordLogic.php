<?php
declare(strict_types=1);

namespace App\Model\Logic;

use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Collection;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * 聊天记录流程
 *
 * Class ChatRecordLogic
 * @package App\Model\Logic
 * Created by lujianjin
 * Data: 2020/10/4
 * Time: 15:08
 *
 * @Bean()
 */
class ChatRecordLogic
{
    /**
     * @var
     */
    private $mongodbClient;

    public function getChatRecord(string $openid, string $beginTime, string $endTime, int $page = 1, int $limit = 10, string $keyword = ''): array
    {
        /*
         * 1. 获取开始时间和借宿时间之间有多少个月份
         * 2. 分别连接这些月份来获取数据
         * 3. 返回数据
         */
        $dataList = $this->getDataList($beginTime, $endTime, $openid, $page, $limit, $keyword);

        //格式化处理数据
        $this->formData($dataList);

        return $dataList;
    }


//    /**
//     * Notes: 获取微医生聊天记录列表
//     *
//     * @author: lujianjin
//     * datetime: 2020/10/7 13:11
//     */
//    public function getChatList(string $customUuid, int $year, int $month, int $page = 1, int $limit = 20): array
//    {
//        // 获取client
//    }


    /**
     * Notes: 读消息
     *
     * @param string $mongoId
     * @return bool
     * @author: lujianjin
     * datetime: 2020/10/4 15:34
     */
    public function readMessage(string $mongoId): bool
    {
        // 获取mongo连接
        $client = $this->getMongoDbClient();
        // 本月1号时间戳
        $time = strtotime(date('Y-m-1'));

        while (1) {
            $collection = $client->{'chat_' . date('Y', $time)}->{'month_' . date('m', $time)};

            // 修改数据
            $updateResult = $collection->updateOne(['_id' => new ObjectId($mongoId)], ['$set' => ['is_read' => true]]);

            if ($updateResult->getModifiedCount()) {
                // 修改成功了直接返回true
                return true;
            }

            if ($collection->countDocuments() === 0) {
                // 如果集合不存在，直接返回false
                return false;
            }

            $time = strtotime('-1 month', $time);
        }

        return false;
    }


    /**
     * Notes: 将时间转换为月份的数组
     *
     * @param $beginTime
     * @param $endTime
     * @return array
     * @author: lujianjin
     * datetime: 2020/10/4 15:37
     */
    private function timeToMonthArr(string $beginTime, string $endTime): array
    {
        $monthArr = [];
        if (empty($beginTime) || empty($endTime)) {
            return [];
        }

        $time = strtotime(date('Y-m-01'), $beginTime);
        while ($time < $endTime) {
            $monthArr[] = [
                'year'  => date('Y', $time),
                'month' => date('m', $time)
            ];

            $time = strtotime('+1 month', $time);
        }

        return $monthArr;
    }



    /**
     * Notes: 获取MongoDB Client
     *
     * @author: lujianjin
     * datetime: 2020/10/7 13:10
     * @return Client
     */
    private function getMongoDbClient()
    {
        if ($this->mongodbClient) {
            return $this->mongodbClient;
        }

        $this->mongodbClient = new Client(env('MONGODB_DSN', ''));

        return $this->mongodbClient;
    }


    /**
     * Notes: 查询数据
     *
     * @author: lujianjin
     * datetime: 2020/10/7 13:08
     * @param string $beginTime
     * @param string $endTime
     * @param string $openid
     * @param int $page
     * @param int $limit
     * @param string $keyword
     * @return array
     */
    private function getDataList(string $beginTime, string $endTime, string $openid, int $page, int $limit, string $keyword = ''): array
    {
        // 查询总条数
        $count = 0;

        // 偏移量
        $offset = ($page - 1) * $limit;

        // mongodb client
        $mongodbClient = $this->getMongoDbClient();

        // 月份列表
        $monthArr = $this->timeToMonthArr(strtotime($beginTime), strtotime($endTime));

        // 数据保存
        $dataList = [];

        // 查询条件
        $where = ['openid' => $openid];
        if (!empty($keyword)) {
            $where['data'] = $keyword;

        }
        // 时间区间查询
        $where['created_time'] = ['$gte' => $beginTime, '$lt' => $endTime];

        $monthArr = array_reverse($monthArr);
        foreach ($monthArr as $monthItem) {
            // 查询数据
            /** @var Collection $collection */
            $collection = $mongodbClient->{'chat_' . $monthItem['year']}->{'month_' . $monthItem['month']};

            // 当前集合条数
            $currentCount = $collection->countDocuments($where);

            // 总条数
            $count += $currentCount;
            // 判断数量是否已经到达偏移量
            if ($count > $offset) {
                // 开始查询数据了
                $option = [
                    // 当前集合偏移量 = 当前集合总数 - (当前总数 - (初始偏移量 + 以获取数组长度))
                    'skip'  => ($skip = $currentCount - ($count - ($offset + count($dataList)))) < 0 ? 0 : $skip, // 偏移量
                    'limit' => $limit - count($dataList),
                    'sort' => ['created_time' => -1]
                ];

                $data = $collection->find($where, $option)->toArray();

                $dataList = array_merge($dataList, $data);

                // 如果查询数够了则推退出循环
                if (count($dataList) >= $limit) {
                    break;
                }
            }

        }

        return $dataList;
    }


    /**
     * Notes: 格式化数据
     * 去掉MongoDBid 和 解析data字符串
     *
     * @param array $dataList
     * @author: lujianjin
     * datetime: 2020/10/7 12:59
     */
    private function formData(array &$dataList): void
    {
        foreach ($dataList as &$value) {
            if (isset($value['_id'])) {
                $value['_id'] = (string)$value['_id'];

            }

            if (isset($value['data'])) {
                $value['data'] = json_decode($value['data'], true);

            }
        }
    }


}
