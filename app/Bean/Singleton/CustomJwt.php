<?php


namespace App\Bean\Singleton;


use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * 客服jwt验证
 *
 * Class CustomJwt
 * @package App\Bean\Singleton
 * Created by lujianjin
 * DataTime: 2020/10/10 21:23
 *
 * @Bean("CustomJwt")
 */
class CustomJwt
{

    /**
     * 私钥文件路径
     *
     * @var
     */
    protected $privateKeyPath;

    /**
     * 公钥文件路径
     *
     * @var
     */
    protected $publicKeyPath;


    public function __construct()
    {
        $this->privateKeyPath = 'file://' . config('private_key_path', '');
        $this->publicKeyPath  = 'file://' . config('public_key_path', '');
    }

}
