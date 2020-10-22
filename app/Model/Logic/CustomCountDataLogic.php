<?php


namespace App\Model\Logic;

use App\Bean\Singleton\RedisKeyManage;
use App\Rpc\Lib\CustomInterface;
use MongoDB\Client;
use MongoDB\Collection;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Redis\Redis;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * 微医生统计数据逻辑代码
 *
 * Class CustomCountDataLogic
 * @package App\Model\Logic
 * Created by lujianjin
 * DataTime: 2020/10/14 18:05
 *
 * @Bean()
 */
class CustomCountDataLogic
{
    /**
     * @Inject()
     * @var CustomCountDataDao
     */
    private $customCountDataDao;

    /**
     * @Inject()
     * @var RedisKeyManage
     */
    private $redisKeyManage;

    /**
     * mongodb client
     *
     * @var Client
     */
    private $mongodbClient;

    /**
     * @Reference()
     * @var CustomInterface
     */
    private $customService;

    /**
     * @Inject()
     * @var CustomDao
     */
    private $customDao;

    /**
     * Notes: 获取MongoDB Client
     *
     * @return Client
     * @author: lujianjin
     * datetime: 2020/10/15 15:12
     */
    private function getMongoDbClient(): Client
    {
        if ($this->mongodbClient) {
            return $this->mongodbClient;
        }

        $this->mongodbClient = new Client(env('MONGODB_DSN', ''));

        return $this->mongodbClient;
    }

    /**
     * Notes: 获取集合
     *
     * @param string $year
     * @param string $month
     * @return Collection
     * @author: lujianjin
     * datetime: 2020/10/15 15:18
     */
    private function getCollection(string $year, string $month): Collection
    {
        return $this->getMongoDbClient()->{'chat_' . $year}->{'month_' . $month};
    }

    public function getMongoDBFirstResponseData(string $date)
    {
        // 日期的时间戳
        $time = strtotime($date);
        // 获取集合
        $collection = $this->getCollection(date('Y', $time), date('m', $time));

        // 聚合查询结果
        $aggregateList = $collection->aggregate([
            [
                '$match' => [
                    'created_time' => ['$gte' => date('Y-m-d 00:00:00', $time), '$lte'=>date('Y-m-d 23:59:59')],
                    'custom_uuid' => ['$in' => $customs], // 查询的微医生列表
                    'send' => 'customer', // 微医生回复的消息
                    'response_time' => ['$nin' => [0, null]], // 回复时间不为0或空
                ]
            ],
            ['$sort'=>['created_time'=>1]],
            [
                '$group'=>[
                    '_id' => ['customer'=>'$custom_uuid','openid'=>'$openid'],
                    'first_response' => ['$first' => '$response_time']
                ],
            ],
        ]);
        // 结果保存数组
        $dataList = [];
        foreach ($aggregateList->toArray() as $item) {
            $item = (array)$item;
            $dataList[$item['_id']] = round($item['first_response_avg'], 2);

        }

        return $dataList;
    }



    public function getMongoDBAvgResponseData(string $date, array $customs)
    {
        // 日期的时间戳
        $time = strtotime($date);
        // 获取集合
        $collection = $this->getCollection(date('Y', $time), date('m', $time));

        // 聚合查询结果
        $aggregateList = $collection->aggregate([
           [
             // 查询筛选
               '$match' => [
                   'created_time' => ['$gte'=>date('Y-m-d 00:00:00', $time), '$lte'=>date('Y-m-d 23:59:59')],
                   'custom_uuid' => ['$in' => $customs],
                   'send' => 'customer',
                   'response_time' => ['$nin'=>[0, null]],
               ]
           ],
            [
                '$group' => [
                    '_id' => '$custom_uuid',
                    'avg_response_time' => ['$avg' => '$response_time']
                ]
            ],
        ]);
        // 结果保存数组
        $dataList = [];
        foreach ($aggregateList->toArray() as $item) {
            $item = (array)$item;
            $dataList[$item['_id']] = round($item['avg_response_time'], 2);
        }
        return $dataList;
    }

    /**
     * Notes: 获取MongoDB漏回复数据
     *
     * @author: lujianjin
     * datetime: 2020/10/15 16:25
     * @param string $date
     * @param array $customs
     * @return array
     */
    public function getMongoDBNotResponseData(string $date, array $customs)
    {
        // 日期的时间戳
        $time = strtotime($date);
        // 获取集合
        $collection = $this->getCollection(date('Y', $time), date('m', $time));

        // 聚合查询结果
        $aggregateLIst = $collection->aggregate([
            [
                // 查询筛选
                '$match' => [
                    'created_time' => ['$gte' => date('Y-m-d00:00:00', $time), '$lte'=>date('Y-m-d 23:59:59')],
                    'custom_uuid' => ['$in'=>$customs],
                    'send' => 'customer',
                    'response_time' => ['$gt' => 100],
                ]
            ],
            [
                '$group' => [
                    '_id' => '$custom_uuid',
                    'not_response_num' => ['$sum'=>1]
                ]
            ],
        ]);

        // 结果保存数组
        $dataList = [];
        foreach ($aggregateLIst->toArray() as $item) {
            $item = (array)$item;
            $dataList[$item['_id']] = round($item['not_response_num'],2);
        }
        return $dataList;
    }

    public function batchGetCustomRedisData(string $date, array $customs)
    {
        $result = Redis::pipeline(function (\Redis $redis) use ($date, $customs) {
            foreach ($customs as $customUUid) {
                // 获取主动咨询人数 1到正无穷
                $redis->zCount($this->redisKeyManage->getUserAdvisoryKey($customUUid, $date), (string)1,'+inf');
                // 获取接待量 0到正无穷
                $redis->zCount($this->redisKeyManage->getUserAdvisoryKey($customUUid, $date),(string)0,'+inf');
            }
        });

        $customData = [];
        // 标记
        $i = 0;
        // 获取数据
        foreach ($customs as $custom) {
            $customData[$custom] = [
              // 这里只能使用后++
                'active_consultation_num' => $result[$i++] ?? 0,
                'reception_volume' => $result[$i++] ?? 0,
            ];
        }
        return $customData;
    }

    /**
     * Notes: 批量获取微医生在线时长
     *
     * @author: lujianjin
     * datetime: 2020/10/15 16:37
     * @param array $customs
     * @param string $date
     * @return mixed
     *      e.g:
     *      [
     *      "ac0cbb64-fdbc-4b03-b1b7-ef0fe5652b2a":100,
     *      "ac0cbb64-fdbc-4b03-b1b7-ef0fe5652b2b":200,
     *      ]
     */
    public function batchGetCustomOnlineTIme(array $customs, string $date)
    {
        // 获取集合Key
        return $this->customDao->batchGetCustomOnlineTime($customs, $date);
    }

    /**
     * Notes: 批量删除微医生在redis中的接待数据
     *
     * @author: lujianjin
     * datetime: 2020/10/15 16:41
     * @param string $date
     * @param array $customs
     */
    public function batchDelCustomRedisData(string $date, array $customs)
    {
        $keys = [];
        foreach ($customs as $customUUid)
        {
            $keys[] = $this->redisKeyManage->getUserAdvisoryKey($customUUid, $date);
        }
        // 删除键
        Redis::del(...$keys);
    }

    /**
     * Notes: 获取微医生统计数据
     *
     * @author: lujianjin
     * datetime: 2020/10/15 18:00
     * @param int $page
     * @param int $limit
     * @param array $condition
     * @param array $dateCondition
     * @return array
     */
    public function getCustomCountData(int $page, int $limit, array $condition, array $dateCondition)
    {
        // 获取微医生数据
        $customList = $this->customService->getCustomInfoPage($page, $limit, $condition);

        if (!isset($customList['list']) || count($customList['list']) < 1) {
            return [];
        }

        $date = '';
        if ($dateCondition['startAt'] === $dateCondition['endAt']) {
            $date = $dateCondition['startAt'];
        }

        $dateCondition['startAt'] .= ' 00:00:00';
        $dateCondition['endAt'] .= ' 23:59:59';

        // 微医生列表
        $list = $customList['list'];
        $data = $this->customCountDataDao->getCustomCountData($dateCondition, array_column($list, 'uuid'));

        // 咨询年卡
        $buyVipRecord = $this->customCountDataDao->getVipRecordDetail(array_column($list, 'uuid'), $dateCondition['startAt'], $dateCondition['endAt']);

        // 咨询成交额
        $payOrderRecord = $this->customCountDataDao->getPayOrderRecord(array_column($list, 'uuid'), $dateCondition['startAt'], $dateCondition['endAt']);

        $dataList = [];
        foreach ($list as $item) {
            $dataList[] = [
                'custom_uuid' => $item['uuid'],
                'account' => $item['account'],
                'custom_name' => $item['custom_name'],
                'date' => $date,
                'reception_volume' => $data[$item['uuid']]['reception_volume'] ?? 0,
                'active_consultation_num' => $data[$item['uuid']]['active_consultation_num'] ?? 0,
                'first_response' => $data[$item['uuid']]['first_response'] ?? 0,
                'avg_response' => $data[$item['uuid']]['avg_response'] ?? 0,
                'not_response_num' => $data[$item['uuid']]['not_response_num'] ?? 0,
                'pay_order_num' => isset($data[$item['uuid']]['pay_order_num']) ? ($data[$item['uuid']]['pay_order_num'] / 100) : 0,
                'year_vip_num' => $data[$item['uuid']]['year_vip_num'] ?? 0,
                'vip_record_record' => $buyVipRecord[$item['uuid']] ?? [],
                'pay_order_record' => $payOrderRecord[$item['uuid']] ?? [],
                'online_time' => isset($data[$item['uuid']['online_time']]) ? floor($data[$item['uuid']]['online_time'] / 60) . '小时' . $data[$item['uuid']]['online_time'] % 60 . '分钟' : 0
            ];
        }

        // 返回数据
        return [
            'list' => $dataList,
            'page' => $page,
            'limit' => $limit,
            'count' => $customList['count']
        ];
    }



    /**
     * Notes: 保存excel文件到runtime/excel,返回文件名
     *
     * @author: lujianjin
     * datetime: 2020/10/15 18:01
     */
    public function customCountDataExport(array $dateCondition, array $condition)
    {
        // 获取微医生数据
        $customList = $this->customService->getCustomInfoPage(1, 9999, $condition);

        // 微医生列表
        $list = $customList['list'];

        $customInfoList = [];
        foreach ($list as $K => $custom) {
            $customInfoList[$custom['uuid']] = $custom;
        }

        // 微医生统计数据
        $data = $this->customCountDataDao->getCustomCountData($dateCondition, array_column($list, 'uuid'));

        // 咨询年卡
        $buyVipRecord = $this->customCountDataDao->getVipRecordDetail(array_column($list, 'uuid'), $dateCondition['startAt'], $dateCondition['endAt']);

        // 咨询成交额
        $payOrderRecord = $this->customCountDataDao->getPayOrderRecord(array_column($list, 'uuid'), $dateCondition['startAt'], $dateCondition['endAt']);

        // 如果不是同一天
        if (date('Y-m-d', strtotime($dateCondition['startAt'])) === date('Y-m-d', strtotime($dateCondition['endAt']))) {
            $date = date('Y-m-d', strtotime($dateCondition['startAt']));
        } else {
            $date = date('Y-m-d', strtotime($dateCondition['startAt'])) . '-' . date('Y-m-d', strtotime($dateCondition['endAt']));

        }

        // -----------    微医生统计数据表     --------------//

        // 头信息
        $header['customData'] = [
            '序号',
            '微医生账号名称',
            '微医生名称',
            '日期',
            '接待量',
            '主动咨询人数',
            '首响/秒',
            '漏回复数/次数',
            '咨询年卡数/数量',
            '咨询成交额',
        ];

        $customData = [];
        foreach ($list as $k => $item) {
            $customData[] = [
                $k,
                $item['account'],
                $item['custom_name'],
                $date,
                $data[$item['uuid']]['reception_volume'] ?? 0,
                $data[$item['uuid']]['active_consultation_num'] ?? 0,
                $data[$item['uuid']]['first_response'] ?? 0,
                $data[$item['uuid']]['not_response_num'] ?? 0,
                $data[$item['uuid']]['year_vip_num'] ?? 0,
                isset($data[$item['uuid']]['pay_order_num']) ? ($data[$item['uuid']]['pay_order_num'] / 100) : 0
            ];
        }

        // 获取初始列和最终列
        $firstColumn = strtoupper(chr(65));
        $lastColumn = strtoupper(chr(65 + count($header['customData'])));
        // 获取初始行和最终行
        $firstRow = 1;
        $lastRow = count($customData) + 1;
        $row = 1;
//        $spreadsheet = new Spreads
    }
}
