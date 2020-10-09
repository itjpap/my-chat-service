<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\WebSocket;

use App\WebSocket\Controller\HomeController;
use Swoft\Http\Message\Request;
use Swoft\Session\Session;
use Swoft\WebSocket\Server\Annotation\Mapping\OnMessage;
use Swoft\WebSocket\Server\Annotation\Mapping\OnOpen;
use Swoft\WebSocket\Server\Annotation\Mapping\WsModule;
//use App\WebSocket\Parser\JsonParser;
use App\WebSocket\Parser\JsonParser;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use function basename;
use function server;

/**
 * Class ChatModule
 *
 * @WsModule(
 *     "/chat",
 *     defaultCommand="home.index",
 *     messageParser=JsonParser::class,
 *     controllers={HomeController::class}
 * )
 */
class ChatModule
{
    /**
     * @OnOpen()
     * @param Request $request
     * @param int     $fd
     */
    public function onOpen(Request $request, int $fd): void
    {
        server()->push($request->getFd(), "Opened, welcome!(FD: $fd)");

//        $fullClass = Session::current()->getParserClass();
//        $className = basename($fullClass);

//        server()->push($fd, 'in onOpen');
    }

//
//    /**
//     * Notes:
//     *
//     * @author: lujianjin
//     * datetime: 2020/10/8 20:29
//     *
//     * @OnMessage()
//     */
//    public function onMessage(Server $server, Frame $frame)
//    {
//        var_dump('this is frame->data:' . $frame->data);
//    }
}
