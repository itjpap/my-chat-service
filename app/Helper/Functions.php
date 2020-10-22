<?php declare(strict_types=1);

use Swoft\Context\Context;
use Swoft\Http\Message\ContentType;
use Swoft\WebSocket\Server\Message\Response;

/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

function user_func(): string
{
    return 'hello';
}

/**
 * Notes: 根据service，返回连接的数据库
 *
 * @author: lujianjin
 * datetime: 2020/10/15 19:12
 * @param $service
 * @return string
 */
function get_database_instance($service)
{
    $instance = 'db.pool';
    $serviceArr = config('service_db');
    if (array_key_exists($service, $serviceArr)) {
        $instance = (string)$serviceArr[$service];
    }
    return $instance;
}

/**
 * Notes:
 *
 * @author: lujianjin
 * datetime: 2020/10/15 19:14
 * @param $service
 * @return int|mixed
 */
function get_service_id($service)
{
    $id = 0;
    $serviceArr = config('service');
    if (array_key_exists($service, $serviceArr)) {
        $id = $serviceArr[$service];
    }
    return $id;
}


/**
 * Notes:
 *
 * @author: lujianjin
 * datetime: 2020/10/15 19:16
 * @param $key
 * @param $service
 * @param string $default
 * @return string
 */
function get_config($key, $service, $default = '')
{
    $value = $default;
    $serviceArr = config($key);
    if (array_key_exists($service, $serviceArr)) {
        $value = (string)$serviceArr[$service];
    }
    return $value;
}

/**
 * Notes:
 *
 * @param array $data
 * @param int $httpCode
 * @return \Swoft\Http\Message\Response|Response
 *@author: lujianjin
 * datetime: 2020/10/15 19:18
 */
function returnJson(array $data,int $httpCode = 200)
{
    return Context::get()
        ->getResponse()
        ->withContentType(ContentType::JSON)
        ->withStatus($httpCode)
        ->withData($data);
}

/**
 * Notes:
 *
 * @author: lujianjin
 * datetime: 2020/10/15 19:21
 * @param $data
 * @param int $code
 * @return \Swoft\Http\Message\Response|Response
 */
function message($data, $code = 200)
{
    return Context::mustGet()
        ->getResponse()
        ->withContentType(ContentType::HTML)
        ->withStatus($code)
        ->withData($data);
}


function send_websocket_message($fd, $code, $msg, $data = [], $status = 0, $isBinary = 0)
{
    $msg = [
        'code' => $code,
        'message' => $msg,
        'status' => $status,
    ];

    if (!empty($data)) {
        $msg['data'] = $data;
    }
    $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
    if ($isBinary) {
        $length = mb_strlen($msg);
        $binary = '';
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($msg, $i, 1);
            $int = mb_ord($char, 'utf-8');
            charToInt($int, function ($int) use (&$binary) {
                $binary .= pack("C", $int);
            });
        }
        server()->push($fd, $binary, WEBSOCKET_OPCODE_BINARY);
    } else {
        server()->push($fd, $msg);
    }
}

/**
 * Notes: 将utf8整型的数据转为utf8字符
 *
 * @param callable $src
 * @param callable $dst
 * @return string
 * @author: lujianjin
 * datetime: 2020/10/15 19:46
 */
function intToChar(callable $src, callable $dst)
{
    $str = '';
    while (null !== ($a = $src())) {
        if (($a&0x80) === 0) {
            $str .= $dst($a);
        } elseif (($a&0xE0) === 0xc0) {
            if (($b = $src()) === null) {
                throw new RuntimeException('Illegal starting byte:' . $a);
            }
            $str .= $dst(($a&0x1F)<<6 | ($b&0x3F));
        } elseif (($a&0xF0) === 0xE0) {
            if ($b=$src() === null || ($c=$src() === null)) {
                throw new RuntimeException('Illegal starting byte:' . $a);
            }
            $str .= $dst(($a&0x0F<<12) | ($b&0x3F)<<6 | ($c&0x3F));
        } elseif (($a&0xF8) === 0xF0) {
            if ($b=$src() === null || ($c=$src()) === null || ($d=$src()) === null) {
                throw new RuntimeException('Illegal starting byte:' . $a);
            }
            $str .= $dst((($a&0x07)<<18) | (($b&0x3F)<<12) | (($c&0x3F)<<6) | ($d&0x3F));
        } else {
            throw new RuntimeException('Illegal starting byte:' . $a);
        }
    }

    return $str;
}


/**
 * Notes: 将整数转为utf8的编码格式
 *
 * @author: lujianjin
 * datetime: 2020/10/15 19:40
 * @param $int
 * @param callable $dst
 */
function charToInt($int, callable $dst)
{
    if ($int < 0x80) {
        $dst($int&0x7F);
    } else if ($int < 0x800) {
        $dst((($int>>6)&0x1F)|0xc0);
        $dst(($int&0x3F)|0x80);
    } else if ($int < 0x10000) {
        $dst((($int>>12)&0x0F)|0xE0);
        $dst((($int>>6)&0x3F)|0x80);
        $dst(($int&0x3F)|0x80);
    } else {
        $dst((($int>>18)&0x07)|0xF0);
        $dst((($int>>12)&0x3F)|0x80);
        $dst((($int>>6)&0x3F)|0x80);
        $dst(($int&0x3F)|0x80);
    }
}
