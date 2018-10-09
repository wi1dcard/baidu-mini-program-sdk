<?php

namespace BaiduMiniProgram\Services;

/**
 * 消息模版
 *
 * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/
 */
class BaiduTemplate extends BaiduAbstractService
{
    protected function baseUri()
    {
        return 'https://openapi.baidu.com/rest/2.0/smartapp/template/';
    }

    /**
     * 验证 Offset 和 Count 参数
     *
     * @param string $offset
     * @param string $count
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
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
     * @param string|int $offset 偏移数量
     * @param string|int $count  返回数量，取值区间 (0,20]
     *
     * @return array
     *
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#getTemplateLibraryList/
     */
    public function library($offset = 0, $count = 20)
    {
        $this->validateOffsetCount($offset, $count);

        return $this->serviceClient->request('librarylist', [
            'offset' => $offset,
            'count'  => $count,
        ]);
    }

    /**
     * 获取模板库某个模板标题下的关键词库
     *
     * @param string $id 模板库 ID
     *
     * @return array
     *
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#getTemplateLibraryById/
     */
    public function find($id)
    {
        return $this->serviceClient->request('libraryget', [
            'id' => $id,
        ]);
    }

    /**
     * 组合模板并添加至小程序下的模板库
     *
     * @param string $id       模板库 ID
     * @param array  $keywords 模板关键词 ID 数组，如 [1,2,3]
     *
     * @return array
     *
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#addTemplate/
     */
    public function add($id, $keywords)
    {
        $numberOfKeywords = count($keywords);
        if ($numberOfKeywords < 2 || $numberOfKeywords > 6) {
            throw new \InvalidArgumentException('Invalid number of keywords, valid range: [2,6].');
        }

        return $this->serviceClient->request('templateadd', [
            'id'              => $id,
            'keyword_id_list' => json_encode($keywords),
        ]);
    }

    /**
     * 获取小程序下已存在的模板列表
     *
     * @param string|int $offset 偏移数量
     * @param string|int $count  返回数量
     *
     * @return array
     *
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#getTemplateList/
     */
    public function get($offset = 0, $count = 20)
    {
        $this->validateOffsetCount($offset, $count);

        return $this->serviceClient->request('templatelist', [
            'offset' => $offset,
            'count'  => $count,
        ]);
    }

    /**
     * 删除小程序下的某个模板
     *
     * @param string $templateId 模板 ID
     *
     * @return array
     *
     * @see https://smartprogram.baidu.com/docs/develop/api/open_infomation/#deleteTemplate/
     */
    public function delete($templateId)
    {
        return $this->serviceClient->request('templatedel', [
            'template_id' => $templateId,
        ]);
    }
}
