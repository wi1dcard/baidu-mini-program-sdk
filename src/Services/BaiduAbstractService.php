<?php

namespace BaiduMiniProgram\Services;

use BaiduMiniProgram\Client\BaiduServiceClient;

abstract class BaiduAbstractService
{
    /**
     * 百度服务客户端
     *
     * @var BaiduServiceClient
     */
    public $client;

    /**
     * 创建服务对象
     *
     * @param BaiduServiceClient $client
     */
    public function __construct(BaiduServiceClient $client)
    {
        $client->setBaseUri($this->baseUri());

        $this->client = $client;
    }

    abstract protected function baseUri();
}
