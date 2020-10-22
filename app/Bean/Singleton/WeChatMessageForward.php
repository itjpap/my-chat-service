<?php


namespace App\Bean\Singleton;


use EasyWeChat\Kernel\Http\StreamResponse;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use EasyWeChat\OfficialAccount\Application as OfficialAccountApplication;

/**
 * Class WeChatMessageForward
 * @package App\Bean\Singleton
 * Created by lujianjin
 * DataTime: 2020/10/20 15:31
 *
 * @Bean()
 */
class WeChatMessageForward
{

    /**
     * @Inject()
     * @var SendMessage
     */
    private $sendMessage;

    public function forward(string $token, string $oldCustomId, $app)
    {
        if ($app instanceof OfficialAccountApplication) {
            // 传入的参数
            $content = $app->server>getMessage();

            // 旧的微医生id转换uuid
            $customUUid = $this->customData->getCustomUUidByOldId($oldCustomId, $token);

            if (!empty($content['FromUserName']) && $customUUid) {
                // 将媒体信息上传到本地
                $content = $this->media($app, $content);

                // 解析数据
                $messageContent = $this->parseContent($content, $token);

                // 是否保存聊天记录
                $saveRecord = true;
                if ($content['MsgType'] ?? '') {
                    $saveRecord = false;
                    if (
                        in_array($content['EventKey'] ?? '', ['CUSTOMER', '联系客服', '我要找小希', '人工客服']) ||
                        in_array($content['Event'] ?? '', ['subscribe', 'unsubscribe'])
                    ) {
                        $saveRecord = true;
                    }
                }
                $this->sendMessage->sendMessageToCustom($customUUid, $token, $messageContent, $saveRecord);
            }
        }
    }

    /**
     * Notes:
     *
     * @author: lujianjin
     * datetime: 2020/10/20 16:22
     * @param OfficialAccountApplication $app
     * @param array $payload
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    private function media(OfficialAccountApplication $app, array $payload)
    {
        if (isset($payload['MediaId'])) {
            $stream = $app->medis->get($payload['MediaId']);
            if ($stream instanceof StreamResponse) {
                // 以内容 MD5 为文件名存到本地
                $fileName = $stream->save(\Swoft::$app->getPath('public/upload/media/user'));
                $payload['mediaUrl'] = '/public/upload/media/user/' . $fileName;
            }
        }

        return $payload;
    }

    /**
     * Notes: 解析数据将数据解析为发送给微医生的格式
     *
     * @param array $content
     * @param string $token
     * @return mixed
     * @author: lujianjin
     * datetime: 2020/10/20 16:22
     */
    private function parseContent(array $content, string $token)
    {
        $messageContent['msg_source'] = $content['FromUserName'] ?? '';
        switch ($content['MsgType'] ?? '') {
            case 'text':
                $messageContent['msg_type'] = 'text';
                $messageContent['content'] = $content['Content'];
                break;
            case 'image':
                $messageContent['msg_type'] = 'image';
                $messageContent['content'] = $content['mediaUrl'] ?? '';
                break;
            case 'voice':
                $messageContent['msg_type'] = 'voice';
                $messageContent['content'] = $content['mediaUrl'] ?? '';
                break;
            case 'video':
            case 'shortvideo':
                $messageContent['msg_type'] = 'video';
                $messageContent['content'] = $content['mediaUrl'] ?? '';
                break;

            case 'location':
                $messageContent['msg_type'] = 'location';
                $messageContent['content'] = [
                    'location_x' => $content['Location_X'],
                    'location_y' => $content['Location_Y'],
                    'label' => $content['Label'],
                ];
                break;
            case 'link':
                $messageContent['msg_type'] = 'link';
                $messageContent['content'] = [
                  'title' => $content['Title'],
                  'description' => $content['Description'],
                  'url' => $content['Url']
                ];
                break;

            case 'event':
                $messageContent['msg_type'] = 'even'; // 事件处理
                $str = '';
                switch ($content['Event'] ?? '') {
                    case 'subscribe':
                        $str = '系统提示：用户关注了公众号';
                        break;
                    case 'unsubscribe':
                        $str = '系统提示：用户取消关注了公众号';
                        break;
                    case 'SCAN':
                        $str = '系统提示：用户扫描了公众号二维码';
                        break;
                    case 'CLICK':
                        $str = '系统提示：用户点击了菜单：' . ($content['EventKey'] ?? '');
                        break;
                    case 'VIEW':
                        $str = '系统提示：用户点击菜单跳转到：' . $this->menuDao->getUrlToTitle($content['EventKey'] ?? '', $token);
                        break;
                }

                $messageContent['content'] = $str;
                break;
            default:
                $messageContent['msg_type'] = 'text';
                $messageContent['content'] = '';
                break;
        }

        return $messageContent;
    }
}
