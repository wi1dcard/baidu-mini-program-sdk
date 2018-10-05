<?php

namespace BaiduMiniProgram\Payment;

use BaiduMiniProgram\Exceptions\BaiduOpenSslException;
use BaiduMiniProgram\Exceptions\BaiduBase64Exception;
use BaiduMiniProgram\Exceptions\BaiduInvalidSignException;

class Signer
{
    /**
     * 签名算法
     *
     * @var integer
     */
    protected $algo;

    /**
     * 创建签名器
     *
     * @param integer $algo
     */
    public function __construct($algo = OPENSSL_ALGO_SHA256)
    {
        $this->algo = $algo;
    }

    /**
     * 签名（计算 Sign 值）
     *
     * @param string   $data
     * @param resource $privateKey
     *
     * @throws BaiduOpenSslException
     * @throws BaiduBase64Exception
     *
     * @return string
     *
     * @see https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_2_base/sign_v2.md
     */
    public function generate($data, $privateKey)
    {
        $result = openssl_sign($data, $sign, $privateKey, $this->algo);
        if ($result === false) {
            throw new BaiduOpenSslException();
        }

        return base64_encode($sign);
    }

    /**
     * 将参数数组签名（计算 Sign 值）
     *
     * @param array    $params
     * @param resource $privateKey
     *
     * @return string
     *
     * @see self::generate()
     */
    public function generateByParams($params, $privateKey)
    {
        $data = $this->convertSignData($params);

        return $this->generate($data, $privateKey);
    }

    /**
     * 验签（验证 Sign 值）
     *
     * @param string   $sign
     * @param string   $data
     * @param resource $publicKey
     *
     * @throws BaiduBase64Exception
     * @throws BaiduInvalidSignException
     * @throws BaiduOpenSslException
     *
     * @return void
     */
    public function verify($sign, $data, $publicKey)
    {
        $decodedSign = base64_decode($sign, true);
        if ($decodedSign === false) {
            throw new BaiduBase64Exception($sign);
        }
        $result = openssl_verify($data, $decodedSign, $publicKey, $this->algo);
        switch ($result) {
            case 1:
                break;
            case 0:
                throw new BaiduInvalidSignException($sign, $data);
            case -1:
                // no break
            default:
                throw new BaiduOpenSslException();
        }
    }

    /**
     * 异步通知验签（验证 Sign 值）
     *
     * @param array    $params
     * @param resource $publicKey
     *
     * @return void
     *
     * @see self::verify()
     */
    public function verifyByParams($params, $publicKey)
    {
        $sign = $params['rsaSign'];
        unset($params['rsaSign']);

        $data = $this->convertSignData($params);
        $this->verify($sign, $data, $publicKey);
    }

    /**
     * 将数组转换为待签名数据
     *
     * @param array $params
     *
     * @return string
     */
    protected function convertSignData($params)
    {
        ksort($params);
        $stringToBeSigned = '';
        foreach ($params as $k => $v) {
            $v = @(string) $v;
            if (trim($v) === '' || $v[0] === '@') {
                continue;
            }
            $stringToBeSigned .= "&{$k}={$v}";
        }
        $stringToBeSigned = substr($stringToBeSigned, 1);

        return $stringToBeSigned;
    }
}
