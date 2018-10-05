<?php

namespace BaiduMiniProgram;

use BaiduMiniProgram\Exceptions\BaiduDecryptException;
use BaiduMiniProgram\Exceptions\BaiduOpenSslException;
use BaiduMiniProgram\Exceptions\BaiduResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BaiduClient
{
    use ParseResponseTrait;

    /**
     * App Key
     *
     * @var string
     */
    protected $appKey;

    /**
     * App Secret
     *
     * @var string
     */
    protected $appSecret;

    /**
     * HTTP 客户端
     *
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * 创建小程序实例
     *
     * @param string          $appKey 小程序 App Key，又称 Client ID，可从开发者后台查看 {@link https://smartprogram.baidu.com/docs/introduction/register_prepare/}
     * @param string          $appSecret 小程序 App Secret，又称 Client Secret，可从开发者后台查看 {@link https://smartprogram.baidu.com/docs/introduction/register_prepare/}
     * @param ClientInterface $httpClient HTTP 客户端，用于发送请求
     */
    public function __construct($appKey, $appSecret, ClientInterface $httpClient = null)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->httpClient = $httpClient ?: new Client();
    }

    /**
     * 小程序用户登录，使用 Code 换取 SessionKey 等
     *
     * @param string $code
     * 
     * @return mixed
     * 
     * @see https://smartprogram.baidu.com/docs/develop/api/open_log/#Session-Key/
     */
    public function session($code)
    {
        $request = $this->buildSessionRequest($code);

        $response = $this->httpClient->send($request);

        $content = $this->parseResponse($response, 'error', 'error_description');

        return $content;
    }

    /**
     * 构建 getSessionKeyByCode 请求
     *
     * @param string $code
     *
     * @return RequestInterface
     */
    protected function buildSessionRequest($code)
    {
        $uri = 'https://openapi.baidu.com/nalogin/getSessionKeyByCode';

        $data = [
            'code'      => $code,
            'client_id' => $this->appKey,
            'sk'        => $this->appSecret,
        ];

        $body = \GuzzleHttp\Psr7\build_query($data);

        return new Request('POST', $uri, [], $body);
    }

    /**
     * 关键数据解密
     *
     * @param string $cipherText 待解密数据，即小程序端接口返回的 `data` 字段
     * @param string $iv         加密向量，即小程序端接口返回的 `iv` 字段
     * @param string $sessionKey 密钥，登录时服务端使用 Code 换取
     *
     * @throws \InvalidArgumentException
     * @throws BaiduOpenSslException
     * @throws BaiduDecryptException
     *
     * @return string
     *
     * @see self::session()
     * @see http://smartprogram.baidu.com/docs/develop/api/open_log/#%E7%94%A8%E6%88%B7%E6%95%B0%E6%8D%AE%E7%9A%84%E7%AD%BE%E5%90%8D%E9%AA%8C%E8%AF%81%E5%92%8C%E5%8A%A0%E8%A7%A3%E5%AF%86/
     */
    public function decrypt($cipherText, $iv, $sessionKey)
    {
        list($cipherText, $iv, $sessionKey) = $this->decodeForDecrypting($cipherText, $iv, $sessionKey);

        $plainText = openssl_decrypt($cipherText, 'AES-192-CBC', $sessionKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        if ($plainText === false) {
            throw new BaiduOpenSslException(openssl_error_string());
        }

        // trim pkcs#7 padding
        $pad = ord(substr($plainText, -1));
        $pad = ($pad < 1 || $pad > 32) ? 0 : $pad;
        $plainText = substr($plainText, 0, strlen($plainText) - $pad);

        // trim header
        $plainText = substr($plainText, 16);
        // get content length
        $unpack = unpack('Nlen/', substr($plainText, 0, 4));
        // get content
        $content = substr($plainText, 4, $unpack['len']);
        // get app_key
        $appKey = substr($plainText, $unpack['len'] + 4);

        if ($appKey !== $this->appKey) {
            throw new BaiduDecryptException('Invalid app key.');
        }

        return $content;
    }

    protected function decodeForDecrypting($cipherText, $iv, $sessionKey)
    {
        $cipherText = base64_decode($cipherText);
        $iv = base64_decode($iv);
        $sessionKey = base64_decode($sessionKey);

        if (!$sessionKey || !$iv || !$cipherText) {
            throw new \InvalidArgumentException('Bad base64 decode.');
        }

        return [$cipherText, $iv, $sessionKey];
    }
}
