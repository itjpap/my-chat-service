<?php
declare(strict_types=1);


namespace App\Bean\Singleton;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * EasyWeChat操作微信公众平台
 *
 * Class EasyWeChat
 * @package App\Bean\Singleton
 * Created by lujianjin
 * DataTime: 2020/10/7 17:30
 *
 * @Bean(name="easyWeChat")
 */
class EasyWeChat
{

    /**
     * @var array
     */
    private $appArr;

    /**
     * @Inject()
     * @var EasyWeChatCache
     */
    private $easyWeChatCache;

//    /**
//     * 客服RPC
//     *
//     * @Reference(pool="service.pool")
//     * @var ServiceInterface
//     */
//    private $servicesSys;
}
