<?php

namespace BaiduMiniProgram;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use BaiduMiniProgram\Exceptions\ResponseException;
use function GuzzleHttp\json_decode;

class BaiduClient
{
    /**
     * 小程序 App Key
     *
     * @var string
     * @see https://smartprogram.baidu.com/docs/introduction/register_prepare/
     */
    protected $appKey;

    /**
     * 小程序 App Secret
     *
     * @var string
     * @see https://smartprogram.baidu.com/docs/introduction/register_prepare/
     */
    protected $appSecret;

    /**
     * HTTP 客户端
     *
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * 创建小程序实例
     *
     * @param string $appKey
     * @param string $appSecret
     * @param ClientInterface $httpClient
     */
    public function __construct($appKey, $appSecret, ClientInterface $httpClient = null)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->httpClient = $httpClient ?: new Client();
    }

    public function session($code)
    {
        $request = $this->buildSessionRequest($code);
        
        $response = $this->httpClient->send($request);

        $content = $this->parseSessionResponse($response);

        return $content;
    }

    /**
     * 构建 getSessionKeyByCode 请求
     *
     * @param string $code
     * @return RequestInterface
     * 
     * @see https://smartprogram.baidu.com/docs/develop/api/open_log/#Session-Key/
     */
    protected function buildSessionRequest($code)
    {
        $uri = "https://openapi.baidu.com/nalogin/getSessionKeyByCode";

        $data = [
            'code' => $code,
            'client_id' => $this->appKey,
            'sk' => $this->appSecret,
        ];

        return new Request('POST', $uri, [], $data);
    }

    /**
     * 解析 getSessionKeyByCode 响应
     *
     * @param ResponseInterface $response
     * @return array
     */
    protected function parseSessionResponse(ResponseInterface $response)
    {
        $content = $response->getBody()->getContents();

        $parsed = json_decode($content, true);

        if (isset($parsed['error'])) {
            throw new ResponseException($parsed['error_description']);
        }

        return $parsed;
    }
}