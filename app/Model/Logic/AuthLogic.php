<?php declare(strict_types=1);


namespace App\Model\Logic;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Swoft;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * 登录认证逻辑层
 *
 * Class AuthLogic
 * @package App\Model\Logic
 * @Bean()
 */
class AuthLogic
{

    public function login(string $token) :array
    {
        $time = time();
        if(empty($expiresAt)){
            $expiresAt = (int)strtotime('+1 day',$time);
        }
        $privateKey = new Key(config('jwt.privateKey'));
        $signer = new Sha256();
        $token = (new Builder())->issuedBy(env('ISSUE_HOST'))
            ->permittedFor('PERMIT_HOST')
            ->identifiedBy($token,true)
            ->issuedAt($time)
            ->expiresAt($expiresAt)
            ->withClaim('uid',1)
            ->getToken($signer,$privateKey);

        $publicKey = new Key(config('jwt.publicKey'));
        var_dump($token);
        var_dump($token->verify($signer,$publicKey));
        return [];
    }


    public function getJwtToken()
    {

    }


}
