<?php

namespace BaiduMiniProgram;

use BaiduMiniProgram\Client\BaiduAbstractClient;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class BaiduTemplate extends BaiduAbstractClient
{
    const RESPONSE_FIELD = 'errno';
    const RESPONSE_MESSAGE_FIELD = 'msg';
    const GATEWAY = 'https://openapi.baidu.com/rest/2.0/smartapp/template';

    /**
     * Access Token
     *
     * @var string
     */
    protected $accessToken;

    /**
     * 创建模板对象实例
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
     * 构建消息模板的请求
     *
     * @param array $params
     * @param string $action
     * @return RequestInterface
     */
    protected function buildRequest($params, $action)
    {
        $uri = static::GATEWAY . '/' . $action . '?access_token=' . $this->accessToken;

        return new Request('POST', $uri, [], \GuzzleHttp\Psr7\build_query($params));
    }

    /**
     * 解析消息模板请求的响应
     *
     * @param ResponseInterface $response
     * @return array
     */
    protected function parseTemplateResponse(ResponseInterface $response)
    {
        $result = $this->parseResponse($response, static::RESPONSE_FIELD, static::RESPONSE_MESSAGE_FIELD);

        return $result['data'];
    }

    /**
     * 发送请求并解析响应
     *
     * @param RequestInterface $request
     * @return array
     */
    protected function sendRequestThenParse($request)
    {
        $response = $this->httpClient->sendRequest($request);

        return $this->parseTemplateResponse($response);
    }

    protected function validateOffsetCount($offset, $count)
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException('Invalid offset.');
        }
        if ($count <= 0 || $count > 20) {
            throw new \InvalidArgumentException('Invalid count, valid range: (2,20].');
        }
    }

    /**
     * 获取小程序模板库标题列表
     *
     * @param string|integer $offset 偏移数量
     * @param string|integer $count 返回数量，取值区间 (0,20]
     * @return array
     * 
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#getTemplateLibraryList/
     */
    public function library($offset = 0, $count = 20)
    {
        $this->validateOffsetCount($offset, $count);

        $request = $this->buildRequest([
            'offset' => $offset,
            'count'  => $count,
        ], 'librarylist');

        return $this->sendRequestThenParse($request);
    }

    /**
     * 获取模板库某个模板标题下的关键词库
     *
     * @param string $id 模板库 ID
     * @return array
     * 
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#getTemplateLibraryById/
     */
    public function find($id)
    {
        $request = $this->buildRequest([
            'id' => $id,
        ], 'libraryget');

        $response = $this->httpClient->sendRequest($request);

        return $this->parseTemplateResponse($response);
    }

    /**
     * 组合模板并添加至小程序下的模板库
     *
     * @param string $id 模板库 ID
     * @param array $keywords 模板关键词 ID 数组，如 [1,2,3]
     * @return void
     * 
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#addTemplate/
     */
    public function add($id, $keywords)
    {
        $numberOfKeywords = count($keywords);
        if ($numberOfKeywords < 2 || $numberOfKeywords > 6) {
            throw new \InvalidArgumentException('Invalid number of keywords, valid range: [2,6].');
        }

        $request = $this->buildRequest([
            'id' => $id,
            'keyword_id_list' => json_encode($keywords),
        ], 'templateadd');

        $response = $this->httpClient->sendRequest($request);

        return $this->parseTemplateResponse($response);
    }

    /**
     * 获取小程序下已存在的模板列表
     *
     * @param string|integer $offset 偏移数量
     * @param string|integer $count 返回数量
     * @return array
     * 
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#getTemplateList/
     */
    public function get($offset = 0, $count = 20)
    {
        $this->validateOffsetCount($offset, $count);

        $request = $this->buildRequest([
            'offset' => $offset,
            'count'  => $count,
        ], 'templatelist');

        return $this->sendRequestThenParse($request);
    }

    /**
     * 删除小程序下的某个模板
     *
     * @param string $templateId
     * 
     * @return array
     * 
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#deleteTemplate/
     */
    public function delete($templateId)
    {
        $request = $this->buildRequest([
            'offset' => $offset,
            'count'  => $count,
        ], 'templatedel');

        return $this->sendRequestThenParse($request);
    }

    public function sendMessage()
    {
    }
}
