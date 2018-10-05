<?php

use PHPUnit\Framework\TestCase;
use BaiduMiniProgram\BaiduClient;

class ClientTest extends TestCase
{
    public function testCreate()
    {
        $this->assertNotEmpty(
            $appSecret = getenv('APP_SECRET')
        );

        return new BaiduClient('BNQfGXeHyAt83x0qmoK6hvOEdYwCjfeg', $appSecret);
    }

    public function testDecrypt()
    {
        $baidu = new BaiduClient('y2dTfnWfkx2OXttMEMWlGHoB1KzMogm7', '');

        $data = $baidu->decrypt(
            file_get_contents(__DIR__ . '/cipher.txt'),
            '1df09d0a1677dd72b8325Q==',
            '1df09d0a1677dd72b8325aec59576e0c'
        );

        $this->assertEquals(
            '{"openid":"open_id","nickname":"baidu_user","headimgurl":"url of image","sex":1}',
            $data
        );
    }

    /**
     * @depends testCreate
     */
    public function testOauth(BaiduClient $baidu)
    {
        $credential = $baidu->oauth();

        $this->assertArrayHasKey('access_token', $credential);
        $this->assertArrayHasKey('expires_in', $credential);
        $this->assertArrayHasKey('refresh_token', $credential);
        $this->assertArrayHasKey('session_key', $credential);
        $this->assertArrayHasKey('session_secret', $credential);
    }

    /**
     * @depends testCreate
     * @expectedException BaiduMiniProgram\Exceptions\BaiduResponseException
     */
    public function testBadCode(BaiduClient $baidu)
    {
        $baidu->session('bad code');
    }
}