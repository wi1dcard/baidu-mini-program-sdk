<?php

namespace BaiduMiniProgram\Client;

use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BaiduServiceClient extends BaiduAbstractClient
{
    /**
     * Access Token
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Base URI
     *
     * @var string
     */
    protected $baseUri;

    /**
     * 创建服务实例
     *
     * @param string     $accessToken
     * @param HttpClient $httpClient
     */
    public function __construct($accessToken, HttpClient $httpClient = null)
    {
        $this->accessToken = $accessToken;

        parent::__construct($httpClient);
    }

    /**
     * 设置 Base URI
     *
     * @param string $baseUri
     *
     * @return void
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * 获取 Base URI
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * 构建服务请求
     *
     * @param string       $action
     * @param array|object $parameters
     *
     * @return RequestInterface
     */
    public function buildServiceRequest($action, $parameters = [])
    {
        $uri = $this->baseUri . $action . '?access_token=' . $this->accessToken;

        return new Request('POST', $uri, [], \GuzzleHttp\Psr7\build_query($parameters));
    }

    /**
     * 解析服务响应
     *
     * @param ResponseInterface $response
     *
     * @return array
     */
    public function parseServiceResponse(ResponseInterface $response)
    {
        $result = parent::parseResponse($response, 'errno', 'msg');

        return $result['data'];
    }

    /**
     * 发送请求并解析响应
     *
     * @param RequestInterface $request
     *
     * @return array
     */
    public function send(RequestInterface $request)
    {
        $response = $this->httpClient->sendRequest($request);

        return $this->parseServiceResponse($response);
    }

    /**
     * 构建、发送请求并解析响应
     *
     * @param string       $action
     * @param array|object $parameters
     *
     * @return array
     */
    public function request($action, $parameters)
    {
        $request = $this->buildServiceRequest($action, $parameters);

        return $this->send($request);
    }
}
