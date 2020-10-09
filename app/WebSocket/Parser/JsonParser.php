<?php
declare(strict_types=1);


namespace App\WebSocket\Parser;


use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\WebSocket\Server\Contract\MessageParserInterface;
use Swoft\WebSocket\Server\Message\Message;


/**
 * 系统自带的报错，所以自定义一个
 *
 * Class JsonParser
 * @package App\WebSocket\Parser
 * Created by lujianjin
 * DataTime: 2020/10/8 14:06
 *
 * @Bean()
 */
class JsonParser implements MessageParserInterface
{

    /**
     * Encode message data to string.
     *
     * @param Message $message
     *
     * @return string
     */
    public function encode(Message $message): string
    {
        return json_encode($message->toArray());

    }



    /**
     * Decode swoole Frame to Message object
     *
     * @param string $data Message data
     *
     * @return Message
     */
    public function decode(string $data): Message
    {
        $cmd = '';
        $ext = [];
        $map = json_decode($data, true);

        // 如果data不是json格式，拼装data参数并返回
        if (!$map) {
            return Message::new($cmd, $data, $ext);

        }

        if (isset($map['cmd'])) {
            $cmd = (string)$map['cmd'];
            unset($map['cmd']);

        }

        if (isset($map['data'])) {
            $data = $map['data'];
            $ext = $map['ext'] ?? [];

        } else {
            $data = $map;

        }


        return Message::new($cmd, $data, $ext ?: []);

    }
}
