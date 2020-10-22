<?php


namespace App\Bean\Sigleton\Upload;


use OSS\OssClient;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Http\Message\Upload\UploadedFile;


/**
 * Class UploadOss
 * @package App\Bean\Sigleton\Upload
 * Created by lujianjin
 * DataTime: 2020/10/10 18:05
 *
 * @Bean()
 */
class UploadOss implements UploadInterface
{

    /**
     * Notes: 上传文件到oss
     *
     * @author: lujianjin
     * datetime: 2020/10/10 18:07
     * @param string $path
     * @param UploadedFile $file
     * @return string
     */
    public function save(string $path, UploadedFile $file): string
    {
        $ossClient = new OssClient('','','oss-cn-hangzhou.aliyuncs.com');

        $result = $ossClient->uploadFile('kenify', $path . $this->setFileName($file->toArray()['fileName']), $file->toArray()['file']);

        return $result['info']['url'] ?? '';

    }


    /**
     * Notes: 获取文件名
     *
     * @param string $fileName
     * @return string
     * @author: lujianjin
     * datetime: 2020/10/10 21:11
     */
    private function setFileName(string $fileName)
    {
        return date('YmdHis') . '_' . pathinfo($fileName, PATHINFO_BASENAME);

    }




}
