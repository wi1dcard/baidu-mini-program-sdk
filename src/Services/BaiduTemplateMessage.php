<?php

namespace BaiduMiniProgram\Services;

use BaiduMiniProgram\Client\BaiduServiceClient;

/**
 * 模板消息
 *
 * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#sendTemplateMessage/
 */
class BaiduTemplateMessage extends BaiduAbstractService
{
    /**
     * 模板 ID
     *
     * @var string
     */
    protected $templateId;

    /**
     * 模板关键词对应数据
     *
     * @var array|null
     */
    protected $data;

    /**
     * 消息跳转链接
     *
     * @var string
     */
    protected $link;

    /**
     * 创建模板消息
     *
     * @param string             $templateId 模板 ID
     * @param BaiduServiceClient $client
     */
    public function __construct($templateId, BaiduServiceClient $client)
    {
        $this->templateId = $templateId;

        parent::__construct($client);
    }

    protected function baseUri()
    {
        return 'https://openapi.baidu.com/rest/2.0/smartapp/template/';
    }

    /**
     * 携带关键词数据
     *
     * @param array $data 关键词数据数组，例如 [ 'keyword1' => 'eg-value', ... ]
     *
     * @return static
     *
     * @see self::appendKeyword()
     */
    public function withKeywords($data)
    {
        $this->data = [];

        foreach ($data as $key => $value) {
            $this->appendKeyword($key, $value);
        }

        return $this;
    }

    /**
     * 追加一条关键词数据
     *
     * @param string $key   关键词键名，例如 keyword1
     * @param string $value 关键词值
     *
     * @return static
     */
    public function appendKeyword($key, $value)
    {
        $this->data[$key] = ['value' => strval($value)];

        return $this;
    }

    /**
     * 携带链接
     *
     * @param string $link 点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数，例如 index?foo=bar，该字段不填则模板无跳转。
     *
     * @return static
     */
    public function withPage($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * 发送此模板消息
     *
     * @param string $swanId    接收者 swan_id
     * @param string $sceneId   场景 ID，例如表单id和订单id。
     * @param int    $sceneType 场景类型，1：表单，2：百度收银台订单。
     *
     * @return array
     */
    public function sendTo($swanId, $sceneId, $sceneType = 1)
    {
        if (!$this->readyToSend()) {
            throw new \RuntimeException('This template message is not ready to send now.');
        }

        return $this->client->request('send', [
            'template_id' => $this->templateId,
            'touser'      => $swanId,
            'data'        => json_encode($this->data),
            'page'        => $this->link,
            'scene_id'    => $sceneId,
            'scene_type'  => $sceneType,
        ]);
    }

    /**
     * 检查此模板消息是否已准备好发送
     *
     * @return bool
     */
    protected function readyToSend()
    {
        return $this->data !== null;
    }
}
