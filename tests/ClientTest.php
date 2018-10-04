<?php

use PHPUnit\Framework\TestCase;
use BaiduMiniProgram\BaiduClient;

class ClientTest extends TestCase
{
    public function testCreate()
    {
        $this->assertTrue(true);
        
        return new BaiduClient('y2dTfnWfkx2OXttMEMWlGHoB1KzMogm7', '???');
    }

    /**
     * @depends testCreate
     */
    public function testDecrypt(BaiduClient $baidu)
    {
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
}