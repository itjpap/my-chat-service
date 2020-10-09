<?php
declare(strict_types=1);

namespace App\Bean\Singleton;



use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Redis\Pool;

/**
 * 发送数据
 *
 * Class SendMessage
 * Created by lujianjin
 * DataTime: 2020/10/7 15:24
 *
 * @Bean()
 */
class SendMessage
{

//    /**
//     * @Inject("")
//     * @var Pool
//     */
//    private $redis;


//    /**
//     * todo inject()
//     * @var RedisKeyManage;
//     */
//    private $redisKeyManage;


    /**
     * @Inject()
     * @var EasyWeChat
     */
    private $easyWeChat;


    /**
     * @Inject()
     * @var MongoDB
     */
    private $mongoDB;

    /**
     * Notes:发送数据到通道
     *
     * @author: lujianjin
     * datetime: 2020/10/7 15:31
     * @param int $frameId
     * @param $data
     * @return bool
     */
    public function sendMessageToFrame(int $frameId, $data): bool
    {
        return server()->sendTo($frameId, (string)$data);

    }


    /**
     * Notes: 发送用户消息
     *
     * @author: lujianjin
     * datetime: 2020/10/7 15:32
     */
    public function sendUserMessage(array $data, string $time = '')
    {
        return [
            'type' => 'userMessage',
            'content' => $data,
            'time' => $time ?: date('Y-m-d H:i:s')
        ];

    }


    /**
     * Notes: 发送状态信息
     *
     * @author: lujianjin
     * datetime: 2020/10/7 15:34
     */
    public function sendStatusMessage(int $code, bool $status, $msg = '', $content = '', string $time = ''): array
    {
        return [
            'type' => 'status',
            'content' => [
                'code' => $code,
                'status' => $status,
                'msg' => $msg,
                'content' => $content
            ],
            'time' => $time ?: date('Y-m-d H:i:s')
        ];

    }


    private function saveToMongoDB(string $openid, string $customUUid, string $send, string $data, int $responseTime = 0): string
    {
        if (empty($data)) {
            return '';
        }
        // 通过MongoDB保存聊天记录
        $id = $this->mongoDB->save([
            'openid' => $openid,
            'custom_uuid' => $customUUid,
            'send' => $send,
            'is_read' => false,
            'data' => $data,
            'created_at' => date('Y-m-d H:i:s'),
            'response_time' => $responseTime
        ]);

        return $id;

    }


}
