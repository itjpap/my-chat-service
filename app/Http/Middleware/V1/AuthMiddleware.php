<?php declare(strict_types=1);


namespace App\Http\Middleware\V1;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Context\Context;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Contract\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('authorization');
        $public = config('jwt.publicKey');
        $signer = new Sha256();
        $auth = (new Parser())->parse((string)$token);
        $publicKey = new Key($public);
        if($auth->verify($signer,$publicKey)){
            $request->custom = $auth->getClaims();
            $request->token = $auth->getClaim('jti');
        }else{
            return Context::mustGet()->getResponse()->withData('授权失败');
        }
        return $handler->handle($request);
    }


}
