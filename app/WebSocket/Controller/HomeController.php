<?php
declare(strict_types=1);

namespace App\WebSocket\Controller;

use App\Bean\Singleton\RedisKeyManage;
use App\Bean\Singleton\SendMessage;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Session\Session;
use Swoft\WebSocket\Server\Annotation\Mapping\MessageMapping;
use Swoft\WebSocket\Server\Annotation\Mapping\WsController;


/**
 * Class HomeController
 * @package App\WebSocket\Controller
 * Created by lujianjin
 * DataTime: 2020/10/8 18:30
 *
 * @WsController()
 */
class HomeController
{

    /**
     * @Inject()
     * @var SendMessage
     */
    private $wsSendMessage;




    /**
     * Notes:
     * Message command is: 'home.index'
     * @param $data
     * @return bool
     * @author: lujianjin
     * datetime: 2020/10/8 18:24
     *
     * @MessageMapping()
     */
    public function index(string $data)
    {
        $msg = context()->getMessage();
        var_dump('this is data:' . $data);
        var_dump('this is msg:' . $msg);

//        if ($data === 'ping') {
//            Session::mustGet()->push('pong');
//            return true;
//        }
//
//        $jsonData = $this->wsSendMessage->sendStatusMessage(-1, false, '数据格式有误', '数据格式有误');
//        Session::mustGet()->push(json_encode($jsonData));
//
//        return false;

    }


    /**
     * Notes: 发送消息到所有客户端
     *
     * @param string $data
     * @param int $sender
     * @param int $pageSize
     * @author: lujianjin
     * datetime: 2020/10/9 10:11
     *
     * @MessageMapping()
     */
    public function sendToAll(string $data, int $sender = 0, int $pageSize = 50)
    {
        vdump($data);
        server()->sendToAll($data);
    }


    /**
     * Notes:
     * Message command is: 'home.echo'
     * @param string $data
     * @author: lujianjin
     * datetime: 2020/10/8 20:02
     *
     * @MessageMapping()
     */
    public function echo(string $data)
    {
        vdump('in echo:' . $data);
    }


    /**
     * Notes:
     *
     * @param string $data
     * @author: lujianjin
     * datetime: 2020/10/8 20:47
     *
     * @MessageMapping("ar")
     */
    public function autoReply(string $data)
    {
        vdump('in autoReply:' . $data);
    }
}
