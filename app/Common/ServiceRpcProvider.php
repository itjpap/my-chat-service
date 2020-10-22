<?php


namespace App\Common;


use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Consul\Agent;
use Swoft\Consul\Exception\ClientException;
use Swoft\Consul\Exception\ServerException;
use Swoft\Rpc\Client\Client;
use Swoft\Rpc\Client\Contract\ProviderInterface;

/**
 * Class ServiceRpcProvider
 * @package App\Common
 * Created by lujianjin
 * DataTime: 2020/10/14 17:21
 */
class ServiceRpcProvider implements ProviderInterface
{
    /**
     * @Inject()
     *
     * @var Agent
     */
    private $agent;

    private const RPC_NAME = 'service-sys';

    /**
     * Notes:
     *
     * @author: lujianjin
     * datetime: 2020/10/14 17:24
     * @param Client $client
     * @return array
     * @throws ClientException
     * @throws ServerException
     * @example [
     *      'host:port',
     *      'host:port',
     *      'host:port',
     * ]
     */
    public function getList(Client $client): array
    {
        // Get health service from consul
        $services = $this->agent->services();
        $rpcList = $services->getResult();

        $serviceSysList = [];
        foreach ($rpcList as $rpcData) {
            if ($rpcData['Service'] === self::RPC_NAME) {
                $serviceSysList[] = $rpcData['Address'] . ':' . $rpcData['Port'];
            }
        }

        return $serviceSysList;
    }
}
