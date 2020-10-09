<?php
declare(strict_types=1);

namespace App\WebSocket\Controller;


use App\Bean\Singleton\SendMessage;
use App\Model\Logic\ChatRecordLogic;
use Exception;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Session\Session;
use Swoft\WebSocket\Server\Annotation\Mapping\MessageMapping;
use Swoft\WebSocket\Server\Annotation\Mapping\WsController;

/**
 * 发送消息控制器
 *
 * Class MessageController
 * @package App\WebSocket\Controller
 * Created by lujianjin
 * Data: 2020/10/6
 * Time: 13:39
 *
 * @WsController(prefix="message")
 */
class MessageController
{

    /**
     * @Inject()
     * @var SendMessage
     */
    private $sendMessage;

    /**
     * @Inject()
     * @var ChatRecordLogic
     */
    private $chatRecordLogic;


//    /**
//     * Notes: 发送给用户
//     *
//     * @author: lujianjin
//     * datetime: 2020/10/6 13:44
//     */
//    public function sendToUser($data)
//    {
//        $dataArr = (new ParseTextMessage())->parseMessage($data);
//        $data = $dataArr['data'];
//
//        // 微医生uuid
//        $customUUid = Session::mustGet()->get('custom_uuid');
//
//        // 修改最后动态时间
//        $this->customLogic->setLastChatTime($customUUid, time());
//
//        // token
//        $token = Session::mustGet()->get('token');
//
//        // 通道号
//        $fd = Session::mustGet()->getFd();
//
//        // 发送数据给用户
//        $this->sendMessage->sendMessageToUser($token, $customUUid, $data['send_to'], $data, $fd);
//
//        if (in_array($token, ['547bcb48040a7', '54f12086e2afb', '5548411a9dea1'])) {
//            // 修改用户分组
//            sgo(function () use ($data) {
//                try {
//                    SaberGM::get('https://newcrm.wismall.com/NewChat/ChangeUserGroup.html?openId=' . $data['send_to']);
//
//                }  catch (Exception $exception) {
//                    log::error('修改粉丝分组出现异常:' . $exception->getMessage());
//
//                }
//            });
//        }
//    }


    /**
     * Notes: 已读消息
     *
     * @author: lujianjin
     * datetime: 2020/10/6 15:48
     *
     * @MessageMapping(command="read")
     */
    public function read()
    {

    }
}
