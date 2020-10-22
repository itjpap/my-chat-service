<?php


namespace App\Http\Controller\Api\V1;


use App\Model\Logic\ChatRecordLogic;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\Middleware;


/**
 * 聊天记录控制器
 *
 * Class ChatRecordController
 * @package App\Http\Controller\Api\V0
 * Created by lujianjin
 * DataTime: 2020/10/7 13:22
 *
 * @Controller(prefix="/api/v1/chatRecord")
 *
 */
class ChatRecordController
{
    /**
     * @Inject()
     * @var ChatRecordLogic
     */
    private $chatRecordLogic;





}
