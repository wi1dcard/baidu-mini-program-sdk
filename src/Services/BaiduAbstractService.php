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
    protected $serviceClient;

    /**
     * 创建服务对象
     *
     * @param BaiduServiceClient $serviceClient
     */
    public function __construct(BaiduServiceClient $serviceClient)
    {
        $serviceClient->setBaseUri($this->baseUri());

        $this->serviceClient = $serviceClient;
    }

    abstract protected function baseUri();
}
