<?php

use PHPUnit\Framework\TestCase;
use BaiduMiniProgram\BaiduClient;
use BaiduMiniProgram\Client\BaiduServiceClient;
use BaiduMiniProgram\Services\BaiduTemplate;

class ClientTest extends TestCase
{
    /**
     * @var BaiduClient
     */
    protected $baidu;

    public function setUp()
    {
        $this->assertNotEmpty(
            $appSecret = getenv('APP_SECRET')
        );

        $this->baidu = new BaiduClient('BNQfGXeHyAt83x0qmoK6hvOEdYwCjfeg', $appSecret);
    }

    public function testOauth()
    {
        $credential = $this->baidu->oauth();

        $this->assertArrayHasKey('access_token', $credential);
        $this->assertArrayHasKey('expires_in', $credential);
        $this->assertArrayHasKey('refresh_token', $credential);
        $this->assertArrayHasKey('session_key', $credential);
        $this->assertArrayHasKey('session_secret', $credential);

        return $credential;
    }

    /**
     * @expectedException BaiduMiniProgram\Exceptions\BaiduResponseException
     */
    public function testBadCode()
    {
        $this->baidu->session('bad code');
    }

    public function testCode()
    {
        $code = getenv('CODE');

        if ($code == '') {
            $this->assertTrue(true);
            return;
        }

        $data = $this->baidu->session($code);

        $this->assertArrayHasKey('openid', $data);
        $this->assertArrayHasKey('session_key', $data);

        return $data;
    }

    public function testGetServiceClient()
    {
        $client = $this->baidu->serviceClient();

        $this->assertTrue($client instanceof BaiduServiceClient);

        return $client;
    }

    /**
     * @depends testGetServiceClient
     */
    public function testTemplateMethods(BaiduServiceClient $client)
    {
        $tpl = new BaiduTemplate($client);

        $data = $tpl->library();
        $this->assertGreaterThan(2000, $data['total_count']);

        $data = $tpl->find('BD0016');
        $this->assertEquals('取票成功通知', $data['title']);

        $data = $tpl->add('BD0016', [1, 2]);
        $templateId = $data['template_id'];
        $this->assertNotEmpty($templateId);

        $data = $tpl->get();
        $this->assertGreaterThan(0, $data['total_count']);

        $data = $tpl->delete($templateId);
    }
}