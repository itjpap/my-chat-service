<?php


namespace App\Bean\Sigleton\Upload;


use Swoft\Http\Message\Upload\UploadedFile;

class UploadLocal implements UploadInterface
{

    public function save(string $path, UploadedFile $file): string
    {
        $basePath = \Swoft::$app->getPath(env('FILE_SAVE_PATH', 'public/chat/upload/') . $path);
        // 获取文件后缀名
        $fileSuffix = substr($file->getClientFilename(), strrpos($file->getClientFilename(), '.') + 1);
        $fileName = md5(uniqid('chat', true)) . '.' . $fileSuffix;
        $filePath  = $basePath . $fileName;
        $file->moveTo($filePath);

        return $filePath;

    }
}
