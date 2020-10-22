<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Exception;

use Exception;
use Throwable;

/**
 * Class ApiException
 *
 * @since 2.0
 */
class ApiException extends Exception
{
    /**
     * @var int
     */
    protected $httpCode;

    /**
     * api请求异常处理
     *
     * ApiException constructor.
     * @param string $message
     * @param int $code
     * @param int $httpCode
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 40001, $httpCode = 405, Throwable $previous = null)
    {
        $this->httpCode = $httpCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Notes: 获取httpCode
     *
     * @author: lujianjin
     * datetime: 2020/10/20 19:49
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
