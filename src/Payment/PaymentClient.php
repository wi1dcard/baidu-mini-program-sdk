<?php

namespace BaiduMiniProgram\Payment;

use BaiduMiniProgram\ParseResponseTrait;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

class PaymentClient
{
    use ParseResponseTrait;

    /**
     * Deal ID
     *
     * @var string|int
     */
    protected $dealId;

    /**
     * App Key
     *
     * @var string
     */
    protected $appKey;

    /**
     * 应用私钥
     *
     * @var resource
     */
    protected $privateKey;

    /**
     * 平台公钥
     *
     * @var resource
     */
    protected $publicKey;

    /**
     * 签名器
     *
     * @var Signer
     */
    protected $signer;

    /**
     * HTTP 客户端
     *
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * 创建支付客户端
     *
     * @param string|int      $dealId     百度收银台 Deal ID，又称 App ID {@link https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/parameter.md}
     * @param string          $appKey     百度收银台 App Key，此值并非智能小程序平台分配，请不要混淆 {@link https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/parameter.md}
     * @param mixed           $privateKey PEM 格式的应用私钥字符串，或以 `file://` 开头的密钥文件路径  {@link http://php.net/manual/en/function.openssl-pkey-get-private.php}
     * @param mixed           $publicKey  PEM 格式的平台公钥字符串，或以 `file://` 开头的密钥文件路径 {@link http://php.net/manual/en/function.openssl-pkey-get-public.php}
     * @param ClientInterface $httpClient HTTP 客户端，用于发送请求
     * @param Signer          $signer     签名器，用于生成签名、验证签名
     */
    public function __construct(
        $dealId,
        $appKey,
        $privateKey = null,
        $publicKey = null,
        ClientInterface $httpClient = null,
        Signer $signer = null
    ) {
        $this->dealId = $dealId;
        $this->appKey = $appKey;

        $this->privateKey = openssl_pkey_get_private($privateKey);
        $this->publicKey = openssl_pkey_get_public($publicKey);

        $this->httpClient = $httpClient ?: new Client();
        $this->signer = $signer ?: new Signer();
    }

    /**
     * 执行释放密钥等工作
     */
    public function __destruct()
    {
        @openssl_pkey_free($this->privateKey);
        @openssl_pkey_free($this->publicKey);
    }

    /**
     * 获取私钥
     *
     * @throws \LogicException 密钥无效或未初始化时抛出
     *
     * @return resource
     */
    public function getPrivateKey()
    {
        if (is_resource($this->privateKey)) {
            return $this->privateKey;
        }

        throw new \LogicException('Uninitialized private key.');
    }

    /**
     * 获取公钥
     *
     * @throws \LogicException 密钥无效或未初始化时抛出
     *
     * @return resource
     */
    public function getPublicKey()
    {
        if (is_resource($this->publicKey)) {
            return $this->publicKey;
        }

        throw new \LogicException('Uninitialized public key.');
    }

    /**
     * 订单状态查询接口
     *
     * @param string     $orderId
     * @param string|int $userId
     *
     * @return mixed
     */
    public function orderDetail($orderId, $userId)
    {
        $request = $this->buildOrderDetailRequest($orderId, $userId);

        $response = $this->httpClient->send($request);

        return $this->parseResponse($response, 'errno', 'errmsg');
    }

    protected function buildOrderDetailRequest($orderId, $userId)
    {
        $data = [
            'dealId'  => $this->dealId,
            'appKey'  => $this->appKey,
            'orderId' => $orderId,
            'siteId'  => $userId,
        ];

        $data['sign'] = $this->signer->generateByParams($data, $this->getPrivateKey());

        $query = \GuzzleHttp\Psr7\build_query($data);

        $uri = (new Uri('https://dianshang.baidu.com/platform/entity/openapi/queryorderdetail'))
            ->withQuery($query);

        return new Request('GET', $uri);
    }

    /**
     * 申请退款接口
     *
     * @param string     $orderId
     * @param string|int $userId
     * @param string|int $tpOrderId
     * @param int        $refundType
     * @param string     $refundReason
     *
     * @return mixed
     */
    public function orderRefund($orderId, $userId, $tpOrderId, $refundType = 1, $refundReason = '')
    {
        $request = $this->buildOrderRefundRequest($orderId, $userId, $tpOrderId, $refundType, $refundReason);

        $response = $this->httpClient->send($request);

        return $this->parseResponse($response, 'errno', 'msg');
    }

    protected function buildOrderRefundRequest($orderId, $userId, $tpOrderId, $refundType, $refundReason)
    {
        $uri = 'https://nop.nuomi.com/nop/server/rest';

        $data = [
            'method'       => 'nuomi.cashier.applyorderrefund',
            'orderId'      => $orderId,
            'userId'       => $userId,
            'refundType'   => $refundType,
            'refundReason' => $refundType,
            'tpOrderId'    => $tpOrderId,
            'appKey'       => $this->appKey,
        ];

        $data['rsaSign'] = $this->signer->generateByParams($data, $this->getPrivateKey());

        $body = \GuzzleHttp\Psr7\build_query($data);

        return new Request('POST', $uri, [], $body);
    }

    /**
     * 为小程序端发起订单的 `swan.requestPolymerPayment` 接口生成签名
     *
     * @param string|int $tpOrderId
     *
     * @return string
     */
    public function signForPolymerPayment($tpOrderId)
    {
        $params = [
            'appKey'    => $this->appKey,
            'dealId'    => $this->dealId,
            'tpOrderId' => $tpOrderId,
        ];

        return $this->signer->generateByParams($params);
    }

    /**
     * 验证由百度服务器发来的回调通知请求，其签名数据是否未被篡改
     *
     * @param array|null $params 请求参数（默认使用 $_POST）
     *
     * @return bool
     *
     * @see https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/standard_interface/push_notice.md
     * @see https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/standard_interface/refund_audit.md
     * @see https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/standard_interface/success_refund.md
     */
    public function verify($params = null)
    {
        if ($params === null) {
            $params = $_POST;
        }

        try {
            $this->signer->verifyByParams($params, $this->publicKey);
        } catch (AlipayInvalidSignException $ex) {
            return false;
        } catch (\InvalidArgumentException $ex) {
            return false;
        }

        return true;
    }
}
