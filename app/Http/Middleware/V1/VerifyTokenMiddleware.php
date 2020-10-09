<?php


namespace App\Http\Middleware\V1;


use Exception;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Contract\MiddlewareInterface;
use Swoft\Redis\Pool;

/**
 * Class VerifyTokenMiddleware
 * @package App\Http\Middleware\V0
 * @Bean()
 */
class VerifyTokenMiddleware implements MiddlewareInterface
{
    /**
     * @Inject("")
     * @var Pool
     */
    public $redis;



    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getHeaderLine('authorization');
        if(false === strpos($authorization,'Bearer ')){
            throw new \RuntimeException('token验证失败',\BusinessCodeEnum::VERIFY_TOKEN_FALSE);
        }
        $token = str_replace('Bearer ','',$authorization);
        $path = 'file://' . env('PUBLIC_KEY_PATH','');
        try {
            // https://github.com/lcobucci/jwt/blob/3.3/README.md
            $signer = new Sha256();
            $token = (new Parser())->parse((string)$token);
            $publicKey = new Key($path);
            if($token->verify($signer,$publicKey)){

            }else{
                throw new \RuntimeException('token验证失败',\BusinessCodeEnum::VERIFY_TOKEN_FALSE);
            }

        }catch (Exception $exception){
            //todo should optimize
            $data = ['status'=>false,'code'=>40002,'message'=>$exception->getMessage()];
            return $data;
        }


    }
}
