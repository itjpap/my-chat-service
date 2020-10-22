<?php


namespace App\Bean\Sigleton\Upload;


use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Message\Upload\UploadedFile;


/**
 * Class Upload
 * @package App\Bean\Sigleton\Upload
 * Created by lujianjin
 * DataTime: 2020/10/10 17:59
 *
 * @Bean()
 */
class Upload implements UploadInterface
{


    /**
     *
     * @Inject()
     *
     * @var UploadInterface
     */
    private $uploadInterface;


    public function save(string $path, UploadedFile $file): string
    {
        return $this->uploadInterface->save($path, $file);

    }
}
