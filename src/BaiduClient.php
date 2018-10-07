<?php

namespace BaiduMiniProgram;

use BaiduMiniProgram\Client\BaiduAbstractClient;
use BaiduMiniProgram\Exceptions\BaiduDecryptException;
use BaiduMiniProgram\Exceptions\BaiduOpenSslException;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

/**
 * 百度智能小程序
 * 
 * @see https://smartprogram.baidu.com/docs/develop/api/open_log/
 */
class BaiduClient extends BaiduAbstractClient
{
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
     * 创建小程序实例
     *
     * @param string     $appKey     小程序 App Key，又称 Client ID，可从开发者后台查看 {@link https://smartprogram.baidu.com/docs/introduction/register_prepare/}
     * @param string     $appSecret  小程序 App Secret，又称 Client Secret，可从开发者后台查看 {@link https://smartprogram.baidu.com/docs/introduction/register_prepare/}
     * @param HttpClient $httpClient HTTP 客户端，用于发送请求
     */
    public function __construct($appKey, $appSecret, HttpClient $httpClient = null)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        parent::__construct($httpClient);
    }

    /**
     * 服务端发起 OAuth 请求，获取 Access Token，可用于发送模板消息等
     *
     * @return array
     *
     * @see https://smartprogram.baidu.com/docs/develop/server/power_exp/
     */
    public function oauth()
    {
        $request = $this->buildOauthRequest();

        $response = $this->httpClient->sendRequest($request);

        $content = $this->parseResponse($response);

        return $content;
    }

    /**
     * 构建 OAuth 请求
     *
     * @return RequestInterface
     */
    protected function buildOauthRequest()
    {
        $uri = 'https://openapi.baidu.com/oauth/2.0/token';

        $data = [
            'grant_type'    => 'client_credentials',
            'scope'         => 'smartapp_snsapi_base',
            'client_id'     => $this->appKey,
            'client_secret' => $this->appSecret,
        ];

        $body = \GuzzleHttp\Psr7\build_query($data);

        return new Request('POST', $uri, [], $body);
    }

    /**
     * 小程序用户登录，使用 Code 换取 SessionKey，可用于解密关键数据等
     *
     * @param string $code
     *
     * @return array
     *
     * @see https://smartprogram.baidu.com/docs/develop/api/open_log/#Session-Key/
     */
    public function session($code)
    {
        $request = $this->buildSessionRequest($code);

        $response = $this->httpClient->sendRequest($request);

        $content = $this->parseResponse($response, 'errno', 'error_description');

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

    /**
     * 使用 Base64 解码参与解密的参数，可重载此方法实现自定义解码
     *
     * @param mixed $cipherText
     * @param mixed $iv
     * @param mixed $sessionKey
     *
     * @return array
     */
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

    /**
     * 获取 BaiduTemplate 对象
     *
     * @return BaiduTemplate
     */
    public function template()
    {
        $credential = $this->oauth();

        return new BaiduTemplate($credential['access_token'], $this->httpClient);
    }
}
