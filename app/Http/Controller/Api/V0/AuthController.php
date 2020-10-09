<?php declare(strict_types=1);


namespace App\Http\Controller\Api\V1;



use App\Model\Logic\AuthLogic;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;


/**
 * 授权控制器
 *
 * Class AuthController
 *
 * @package App\Http\Controller\Api\V0
 * @Controller(prefix="/api/v1/auth")
 */
class AuthController
{
    /**
     * @Inject()
     * @var AuthLogic
     */
    private $authLogic;

    /**
     * @RequestMapping(route="login")
     */
    public function login()
    {
        return $this->authLogic->login('eaf3597b');
    }

}
