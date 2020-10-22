<?php
declare(strict_types=1);

namespace App\Bean\Sigleton\Upload;


use Swoft\Http\Message\Upload\UploadedFile;

/**
 * 上传文件接口
 *
 * Class UploadInterface
 * @package App\Bean\Sigleton\Upload
 * Created by lujianjin
 * DataTime: 2020/10/10 17:54
 */
interface UploadInterface
{


    /**
     * Notes:
     *
     * @author: lujianjin
     * datetime: 2020/10/10 17:56
     * @param string $path
     * @param UploadedFile $file
     * @return string
     */
    public function save(string $path, UploadedFile $file): string;
}
