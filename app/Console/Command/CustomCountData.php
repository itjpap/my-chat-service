<?php


namespace App\Console\Command;


use App\Rpc\Lib\CustomInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Input\Input;
use Swoft\Db\DB;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class CustomCountData
 * @package App\Console\Command
 * Created by lujianjin
 * DataTime: 2020/10/14 17:30
 *
 * @Command(name="customCountData")
 */
class CustomCountData
{

    /**
     *  客服RPC
     *
     * @Reference(pool='custom.pool')
     * @var CustomInterface
     */
    private $customService;


    /**
     * @Inject()
     * @var CustomCountDataLogic
     */
    private $customCountDataLogin;

    /**
     * @Inject()
     * @var UserBuyEventLogic
     */
    private $userBuyEventLogic;

    /**
     * Notes: 定时统计微医生统计数据
     *
     * @param Input $input
     * @return bool
     * @author: lujianjin
     * datetime: 2020/10/14 17:45
     */
    public function run(Input $input)
    {
        if (!isset($input->getArgs()['date'])) {
            echo '请输入日期';
            return false;
        }

        // 查询日期
        $date = $input->getArgs()['date'];

        $page = 1;
        // 批量获取所有微医生数据
        while (1) {
            $list = $this->customService->getCustomInfoPage($page, 300);
            if (!isset($list['list']) || count($list['list']) < 1) {
                break;
            }
            $list = $list['list'];

            // 获取主动咨询人数
            $customs = array_column($list,'uuid');
            // 获取咨询人数
            $customAdvisoryData = $this->customCountDataLogin->batchGetCustomRedisData($date, $customs);
            // 获取首响
            $customFirstResponse = $this->customCountDataLogin->getMongoDBFirstResponseData($date, $customs);
            // 获取均响数据
            $customAvgResponse = $this->customCountDataLogin->getMongoDBAvgResponseData($date, $customs);
            // 获取漏回复数
            $customNotResponse = $this->userBuyEventLogic->batchGetCustomOrderCount($date, $customs);

            // 获取微医生咨询订单交易额
            $customAdvisoryOrder = $this->userBuyEventLogic->batchGetCustomOrderCount($date, $customs);

            // 获取微医生咨询年卡数
            $customAdvisoryYearCardNumber = $this->userBuyEventLogic->batchGetCustomYearCardNumber($date, $customs);

            // 组合数据
            $dataList = $this->composeData(
                $date,
                $customs,
                $customAdvisoryData,
                $customFirstResponse,
                $customAvgResponse,
                $customNotResponse,
                $customAdvisoryOrder,
                $customAdvisoryYearCardNumber
            );
            /*
             * 很多微医生统计数据都是0，为0的数据不插入，不影响数据统计
             */
            foreach ($dataList as $index => $data) {
                if (((int)$data['reception_volume'] + (int)$data['active_consultation_num'] + (int)$data['first_response'] + (int)$data['avg_response'] + (int)$data['not_response_num']
                        + $data['pay_order_num'] + $data['year_vip_num']) === 0) {
                    unset($dataList[$index]);
                }
            }

            $data = DB::table('custom_count_data')
                ->whereIn('custom_uid', array_column($dataList, 'custom_uuid'))
                ->where('date', '=', $date)
                ->get(['custom_uuid'])->toArray();

            $data = array_column($data, null, 'custom_uuid');

            foreach ($dataList as $key => $item) {
                if (isset($data[$item['custom_uuid']])) {
                    unset($dataList[$key]);
                }
            }

            // 批量插入
            DB::table('custom_count_data')->insert($dataList);

            // 删除redis数据
            DB::table('custom_count_data')->insert($dataList);

            $page++;
        }

        return true;

    }

    private function composeData(string $date,
                                 array $customs,
                                 array $customAdvisoryData,
                                 array $customFirstResponse,
                                 array $customAvgResponse,
                                 array $customNotResponse,
                                 array $customAdvisoryOrder,
                                 array $customAdvisoryYearCardNumber
    )
    {
        $dataList = [];

        foreach ($customs as $custom) {
            $dataList[] = [
                'custom_uuid'             => $custom,
                'date'                    => $date,
                'reception_volume'        => $customAdvisoryData[$custom]['reception_volume'] ?? 0,
                'active_consultation_num' => $customAdvisoryData[$custom]['active_consultation_num'] ?? 0,
                'first_response'          => $customFirstResponse[$custom] ?? 0,
                'avg_response'            => $customAvgResponse[$custom] ?? 0,
                'not_response_num'        => $customNotResponse[$custom] ?? 0,
                'pay_order_num'           => $customAdvisoryOrder[$custom]['sum_price'] ?? 0,
                'year_vip_num'            => $customAdvisoryYearCardNumber[$custom]['count_number'] ?? 0
            ];
        }
        return $dataList;
    }


}
