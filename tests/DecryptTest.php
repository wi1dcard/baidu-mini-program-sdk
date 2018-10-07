<?php

use PHPUnit\Framework\TestCase;
use BaiduMiniProgram\BaiduClient;

class DecryptTest extends TestCase
{
    public function cipherTextProvider()
    {
        return [
            ['OpCoJgs7RrVgaMNDixIvaCIyV2SFDBNLivgkVqtzq2GC10egsn+PKmQ/+5q+chT8xzldLUog2haTItyIkKyvzvmXonBQLIMeq54axAu9c3KG8IhpFD6+ymHocmx07ZKi7eED3t0KyIxJgRNSDkFk5RV1ZP2mSWa7ZgCXXcAbP0RsiUcvhcJfrSwlpsm0E1YJzKpYy429xrEEGvK+gfL+Cw=='],
        ];
    }

    /**
     * @dataProvider cipherTextProvider
     */
    public function testDecrypt($cipherText)
    {
        $baidu = new BaiduClient('y2dTfnWfkx2OXttMEMWlGHoB1KzMogm7', '');

        $data = $baidu->decrypt(
            $cipherText,
            '1df09d0a1677dd72b8325Q==',
            '1df09d0a1677dd72b8325aec59576e0c'
        );

        $this->assertEquals(
            '{"openid":"open_id","nickname":"baidu_user","headimgurl":"url of image","sex":1}',
            $data
        );
    }
}