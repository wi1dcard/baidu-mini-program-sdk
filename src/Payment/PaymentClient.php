<?php

namespace BaiduMiniProgram\Payment;

use BaiduMiniProgram\Client\BaiduAbstractClient;
use BaiduMiniProgram\Exceptions\BaiduInvalidSignException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Http\Client\HttpClient;

class PaymentClient extends BaiduAbstractClient
{
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
     * 创建支付客户端
     *
     * @param string|int $dealId     百度收银台 Deal ID {@link https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/parameter.md}
     * @param string     $appKey     百度收银台 App Key，此值并非智能小程序平台分配，请不要混淆 {@link https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/parameter.md}
     * @param mixed      $privateKey PEM 格式的应用私钥字符串，或以 `file://` 开头的密钥文件路径 {@link http://php.net/manual/en/function.openssl-pkey-get-private.php}
     * @param mixed      $publicKey  PEM 格式的平台公钥字符串，或以 `file://` 开头的密钥文件路径 {@link http://php.net/manual/en/function.openssl-pkey-get-public.php}
     * @param Signer     $signer     签名器，用于生成签名、验证签名
     * @param HttpClient $httpClient HTTP 客户端，用于发送请求
     */
    public function __construct(
        $dealId,
        $appKey,
        $privateKey = null,
        $publicKey = null,
        Signer $signer = null,
        HttpClient $httpClient = null
    ) {
        $this->dealId = $dealId;
        $this->appKey = $appKey;

        $this->privateKey = openssl_pkey_get_private($privateKey);
        $this->publicKey = openssl_pkey_get_public($publicKey);
        $this->signer = $signer ?: new Signer();

        parent::__construct($httpClient);
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
     * @param int        $appId
     * @param string     $orderId
     * @param string|int $userId
     *
     * @return mixed
     */
    public function orderDetail($appId, $orderId, $userId)
    {
        $request = $this->buildOrderDetailRequest($orderId, $userId);

        $response = $this->httpClient->sendRequest($request);

        return $this->parseResponse($response, 'errno', 'errmsg');
    }

    protected function buildOrderDetailRequest($appId, $orderId, $userId)
    {
        $data = [
            'appId'   => $appId,
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

        $response = $this->httpClient->sendRequest($request);

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
            'refundReason' => $refundReason,
            'tpOrderId'    => $tpOrderId,
            'appKey'       => $this->appKey,
        ];

        $data['rsaSign'] = $this->signer->generateByParams($data, $this->getPrivateKey());

        $body = \GuzzleHttp\Psr7\build_query($data);

        return new Request('POST', $uri, [], $body);
    }

    /**
     * 取消核销接口
     *
     * @param int $orderId
     * @param int $userId
     * @param int $type
     *
     * @return mixed
     */
    public function syncOrderStatus($orderId, $userId, $type = 3)
    {
        $request = $this->buildSyncOrderStautsRequest($orderId, $userId, $type);

        $response = $this->httpClient->sendRequest($request);

        return $this->parseResponse($response, 'errno', 'msg');
    }

    protected function buildSyncOrderStautsRequest($orderId, $userId, $type)
    {
        $uri = 'https://nop.nuomi.com/nop/server/rest';

        $data = [
            'method'       => 'nuomi.cashier.syncorderstatus',
            'orderId'      => $orderId,
            'userId'       => $userId,
            'type'         => $type,
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

        return $this->signer->generateByParams($params, $this->privateKey);
    }

    /**
     * 验证由百度服务器发来的回调通知请求签名，通常用于确保数据未被篡改；若签名错误则返回假
     *
     * @param array|null $params 请求参数（默认使用 $_POST）
     *
     * @return bool
     *
     * @see self::verifyOrFail()
     */
    public function verify($params = null)
    {
        try {
            $this->verifyOrFail($params);
        } catch (BaiduInvalidSignException $ex) {
            return false;
        } catch (\InvalidArgumentException $ex) {
            return false;
        }

        return true;
    }

    /**
     * 验证由百度服务器发来的回调通知请求签名，通常用于确保数据未被篡改；若签名错误则抛出异常
     *
     * @param array $params 请求参数（默认使用 $_POST）
     *
     * @return array
     *
     * @see https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/standard_interface/push_notice.md
     * @see https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/standard_interface/refund_audit.md
     * @see https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/standard_interface/success_refund.md
     */
    public function verifyOrFail($params = null)
    {
        if ($params === null) {
            $params = $_POST;
        }

        return $this->signer->verifyByParams($params, $this->publicKey);
    }

    /**
     * 构建成功响应，用于接收到回调通知请求时返回给百度服务器
     *
     * @param array|object $data
     *
     * @return string
     */
    protected function buildSuccessfulResponse($data)
    {
        return json_encode([
            'errno' => 0,
            'msg'   => 'success',
            'data'  => $data,
        ]);
    }

    /**
     * 构建失败响应，用于接收到回调通知请求时返回给百度服务器
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function buildFailedResponse(\Exception $exception)
    {
        return json_encode([
            'errno' => $exception->getCode() ?: -1,
            'msg'   => 'failed',
            'data'  => [],
        ]);
    }

    /**
     * 处理回调通知过程中产生的异常
     *
     * @param \Exception $exception
     * @param callable   $handler
     *
     * @return mixed
     */
    protected function handleNotificationException(\Exception $exception, $handler)
    {
        if ($handler !== null) {
            try {
                return call_user_func($handler, $exception);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * 处理回调通知请求，捕捉全部异常转换为百度服务器可识别的 JSON 响应，可直接输出此方法返回值
     *
     * @param callable $handler      回调函数，成功验证签名后则会调用，应当在此回调内编写业务逻辑，若出现失败应当抛出异常，本方法将会捕捉并处理
     * @param callable $errorHandler 错误回调函数，若执行过程中发生任何错误则会调用，可用于记录日志等
     * @param array    $parameters   请求参数（默认使用 $_POST）
     *
     * @return string
     */
    public function handleNotification($handler, $errorHandler = null, $parameters = null)
    {
        try {
            if (!is_callable($handler)) {
                throw new \InvalidArgumentException('Invalid callback.', -2);
            }

            if ($errorHandler !== null && !is_callable($handler)) {
                throw new \InvalidArgumentException('Invalid callback.', -3);
            }

            $parameters = $this->verifyOrFail($parameters);

            $result = call_user_func($handler, $parameters);

            if (!is_array($result) && !is_object($result)) {
                throw new \UnexpectedValueException('Invalid result type of callback.', -5);
            }
        } catch (\Exception $exception) {
            $this->handleNotificationException($exception, $errorHandler);

            return $this->buildFailedResponse($exception);
        }

        return $this->buildSuccessfulResponse($result);
    }
}
