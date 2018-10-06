<?php

namespace BaiduMiniProgram\Client;

use BaiduMiniProgram\Exceptions\BaiduResponseException;
use Psr\Http\Message\ResponseInterface;

trait ParseResponseTrait
{
    /**
     * 从响应中提取响应体完整字符串
     *
     * @param ResponseInterface $response
     *
     * @return string
     */
    protected function getContentFromResponse(ResponseInterface $response)
    {
        return $response->getBody()->getContents();
    }

    /**
     * JSON 解码
     *
     * @param string $json
     *
     * @return mixed
     */
    protected function decodeJsonResponse($json)
    {
        $data = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new BaiduResponseException(
                'json_decode error: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * 检查响应结果是否正常
     *
     * @param mixed  $data
     * @param string $field
     * @param string $messageField
     *
     * @return void
     */
    protected function determineResponseResult($data, $field, $messageField)
    {
        if (isset($data[$field]) && $data[$field] != 0) {
            $message = isset($data[$messageField]) ? $data[$messageField] : 'Bad response.';
            $code = intval($data[$field]);

            throw new BaiduResponseException($message, $code);
        }
    }

    /**
     * 解析接口响应
     *
     * @param ResponseInterface $response
     * @param string|null       $field
     * @param string|null       $messageField
     *
     * @return array
     */
    protected function parseResponse(ResponseInterface $response, $field = null, $messageField = null)
    {
        $content = $this->getContentFromResponse($response);

        $data = $this->decodeJsonResponse($content);

        if ($field !== null && $messageField !== null) {
            $this->determineResponseResult($data, $field, $messageField);
        }

        return $data;
    }
}
