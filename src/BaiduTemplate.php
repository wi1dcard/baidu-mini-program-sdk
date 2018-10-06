<?php

namespace BaiduMiniProgram;

use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class BaiduTemplate
{
    use ParseResponseTrait;

    const RESPONSE_FIELD = 'errno';
    const RESPONSE_MESSAGE_FIELD = 'msg';

    /**
     * HTTP 客户端
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * 接口 URI
     *
     * @var UriInterface|string
     */
    protected $gateway;

    /**
     * 创建模板对象实例
     *
     * @param string     $accessToken
     * @param HttpClient $httpClient
     */
    public function __construct($accessToken, HttpClient $httpClient = null)
    {
        $this->gateway = "https://openapi.baidu.com/rest/2.0/smartapp/template/librarylist?access_token={$accessToken}";
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
    }

    protected function buildRequest($params)
    {
        return new Request('POST', $this->gateway, [], \GuzzleHttp\Psr7\build_query($params));
    }

    protected function parseTemplateResponse(ResponseInterface $response)
    {
        return $this->parseResponse($response, static::RESPONSE_FIELD, static::RESPONSE_MESSAGE_FIELD);
    }

    public function libraryList($offset = 0, $count = 20)
    {
        $request = $this->buildRequest([
            'offset' => $offset,
            'count'  => $count,
        ]);

        $response = $this->httpClient->sendRequest($request);

        return $this->parseTemplateResponse($response);
    }

    public function libraryFind()
    {
    }

    public function add()
    {
    }

    public function list()
    {
    }

    public function delete()
    {
    }

    public function sendMessage()
    {
    }
}
