<?php

namespace BaiduMiniProgram;

use Psr\Http\Message\ResponseInterface;
use BaiduMiniProgram\Exceptions\BaiduResponseException;

trait ParseResponseTrait
{
    /**
     * 从响应中提取响应体完整字符串
     *
     * @param ResponseInterface $response
     * @return string
     */
    protected function getContentFromResponse(ResponseInterface $response)
    {
        return $response->getBody()->getContents();
    }

    /**
     * 解析 JSON
     *
     * @param string $json
     * @return mixed
     */
    protected function decodeJsonResponse($json)
    {
        return \GuzzleHttp\json_decode($json, true);
    }

    /**
     * 检查响应结果是否正常
     *
     * @param mixed $data
     * @param string $field
     * @param string $messageField
     * @return void
     */
    protected function determineResponseResult($data, $field, $messageField)
    {
        if (isset($data[$field]) && $data[$field] != 0) {
            throw new BaiduResponseException($data[$messageField], intval($data[$field]));
        }
    }

    /**
     * 解析接口响应
     *
     * @param ResponseInterface $response
     * @param string $field
     * @param string $messageField
     *
     * @return array
     */
    protected function parseResponse(ResponseInterface $response, $field, $messageField)
    {
        $content = $this->getContentFromResponse($response);

        $data = $this->decodeJsonResponse($content);

        $this->determineResponseResult($data, $field, $messageField);

        return $data;
    }
}