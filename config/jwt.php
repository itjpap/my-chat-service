<?php declare(strict_types=1);

return [
    'publicKey' => 'file://'.Swoft::$app->getBasePath().env('PUBLIC_KEY_PATH',''),
    'privateKey' => 'file://'.Swoft::$app->getBasePath().env('PRIVATE_KEY_PATH','')
];
