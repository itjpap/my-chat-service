<?php
declare(strict_types=1);

namespace App\Bean\Singleton;


use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * redis的键管理
 * 所有键都必须在这里获取
 *
 * Class RedisKeyManage
 * @package App\Bean\Singleton
 * Created by lujianjin
 * DataTime: 2020/10/7 17:04
 *
 * @Bean()
 */
class RedisKeyManage
{

    /**
     * Notes: 获取发布订阅的键
     *
     * @author: lujianjin
     * datetime: 2020/10/7 17:06
     */
    public function getPubSubKey()
    {
        if (empty($chatNum)) {
            $chatNum = env('CHAT_NUMBER', 'chat01');

        }

        return 'chat:pub_sub:' . $chatNum;
    }


    /**
     * Notes: 获取用户信息
     * type: hash
     *
     * @author: lujianjin
     * datetime: 2020/10/7 17:09
     * @param string $token
     * @param string $openid
     * @return string
     */
    public function getUserInfoKey(string $token, string $openid): string
    {
        return 'chat:' . $token . ':user_info:' . $openid;

    }


    /**
     * Notes: 获取菜单栏url对应的标题键
     *
     * @param string $token
     * @return string
     * @author: lujianjin
     * datetime: 2020/10/7 17:10
     */
    public function getMenuUrlToTitle(string $token): string
    {
        return 'chat:' . $token . ':menuToTitle';

    }


    /**
     * Notes: 获取在线时长
     *
     * @param string $date
     * @return string
     * @author: lujianjin
     * datetime: 2020/10/7 17:13
     */
    public function getOnlineTime(string $date): string
    {
        return 'chat:OnlineTime:' . $date;
    }
}
