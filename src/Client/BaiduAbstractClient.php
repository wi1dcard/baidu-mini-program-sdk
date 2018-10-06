<?php

namespace BaiduMiniProgram\Client;

use Http\Client\HttpClient;

abstract class BaiduAbstractClient
{
    use ParseResponseTrait;

    /**
     * HTTP 客户端
     *
     * @var HttpClient
     */
    protected $httpClient;

    public function __construct(HttpClient $httpClient = null)
    {
        $this->httpClient = $httpClient ?: $this->discoverHttpClient();
    }

    /**
     * 寻找可用的 HTTP 客户端
     *
     * @return HttpClient
     */
    public function discoverHttpClient()
    {
        $client = null;

        if (class_exists('Http\Discovery\HttpClientDiscovery')) {
            try {
                $client = \Http\Discovery\HttpClientDiscovery::find();
            } catch (\Http\Discovery\NotFoundException $ex) {}
        }

        if ($client === null) {
            $client = new BaiduHttpClient();
        }

        return $client;
    } 
}