Baidu Smart Mini-Program SDK for PHP
==========

<img src="https://smartprogram.baidu.com/docs/img/logo.png" height="30px">

🐾 百度小程序第三方 PHP SDK，遵循 PSR-7、支持 PHP 5.4，助力智能小程序开发。

[![License](https://img.shields.io/packagist/l/wi1dcard/baidu-mini-program-sdk.svg)](https://github.com/wi1dcard/baidu-mini-program-sdk)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/wi1dcard/baidu-mini-program-sdk.svg)](https://github.com/wi1dcard/baidu-mini-program-sdk)
[![Packagist](https://img.shields.io/packagist/v/wi1dcard/baidu-mini-program-sdk.svg)](https://packagist.org/packages/wi1dcard/baidu-mini-program-sdk)
[![Build Status](https://travis-ci.org/wi1dcard/baidu-mini-program-sdk.svg?branch=master)](https://travis-ci.org/wi1dcard/baidu-mini-program-sdk)
[![Coverage Status](https://coveralls.io/repos/github/wi1dcard/baidu-mini-program-sdk/badge.svg)](https://coveralls.io/github/wi1dcard/baidu-mini-program-sdk)
[![StyleCI](https://github.styleci.io/repos/151553953/shield?branch=master)](https://github.styleci.io/repos/151553953)

❤️ 本项目 [GitHub](https://github.com/wi1dcard/baidu-mini-program-sdk-php) / [Gitee(码云)](https://gitee.com/wi1dcard/baidu-mini-program-sdk-php)。

🎉 [支付宝开放平台第三方 PHP SDK](https://github.com/wi1dcard/alipay-sdk-php)，[微信小程序转百度小程序注意事项](./WECHAT-TO-BAIDU.md)。

* **目录**
    * [主要目的](#主要目的)
    * [如何使用](#如何使用)
    * [其它资源](#其它资源)
    * [感想](#感想)
    * [协议](#协议)

## 主要目的

目前，百度智能小程序暂时未推出官方 PHP SDK，而仅有的百度收银台 SDK 也只具备生成、验证签名功能，不足以支撑实际开发。

本项目着眼于「小程序」，集成以下功能。

1. [登录](#登录)
2. [解密](#解密)
3. [模版消息](#模版消息)（又称「消息模板」）
4. [支付](#支付)（百度收银台）
5. [支付通知](#支付通知)
6. [深入](#深入)

## 如何使用

**墙裂建议**：阅读以下文档时，请同时阅读对应方法、类的 PHPDoc，我们准备了详细的参考链接和说明。

### 准备

1. 安装。

    ```bash
    composer require wi1dcard/baidu-mini-program-sdk:dev-master
    ```

    > Composer 中国镜像近期处于维护状态；若无法安装，建议使用原版 Packagist 或使用 [Laravel-China 镜像](https://wi1dcard.cn/documents/packagist-mirror-in-china/)。

2. 创建 `BaiduClient`。

    ```php
    use BaiduMiniProgram;

    $app = new BaiduClient('App Key', 'App Secret');
    ```

    `App Key` / `App Secret` 可通过 「[小程序开发者后台](https://smartprogram.baidu.com/mappconsole/main/login)」-「智能小程序首页」-「设置」-「开发设置」查看。

    `BaiduClient` 通常情况会贯穿整条业务，除非你须要在同一套代码内处理多个小程序，否则只需在初始化阶段创建一次即可。

    如无特殊说明，以下 `$app` 均为此处的 `BaiduClient` 实例。

### 登录

详细流程 [官方文档](https://smartprogram.baidu.com/docs/develop/api/open_log/) 解释得十分详尽，遵循 OAuth 2.0、过程类似微信，在此不再赘述。

例如：小程序端通过 `swan.login` 得到 `code`，随后使用 `swan.request` 发送请求，将 `code` 发至我方服务端。

我方服务端示例代码如下。

```php
$credential = $app->session($code);
```

若成功，`$credential` 为数组，格式如下。

```json
{
    "openid": "ABCDEFG123",
    "session_key": "xxxxxx"
}
```

若失败，则会抛出 `BaiduResponseException`。

如无特殊说明，以下 `$credential` 均为此返回值。

### 解密

智能小程序可以通过各种前端接口获取百度提供的开放数据，而这些数据返回给小程序时是加密过的。

例如：小程序端通过 [`swan.getUserInfo`](http://smartprogram.baidu.com/docs/develop/api/open_userinfo/#getUserInfo/) 得到 `data` 和 `iv`，随后使用 `swan.request` 发送请求，将其发至我方服务端解密。

我方服务端示例代码如下。

```php
$decrypted = $app->decrypt($data, $iv, $credential['session_key']);
```

若成功，`$decrypted` 为解密后的原始数据。

### 模版消息

在智能小程序开发者后台创建「消息模板」后，即可发送「模板消息」，过程与微信小程序类似。不过，百度小程序支持调用开放接口增删模板消息，这为部分业务场景提供了更加便捷的解决方案。

根据 [官方文档](https://smartprogram.baidu.com/docs/develop/api/open_infomation/), 相关接口本 SDK 调用例子如下。

```php
use BaiduMiniProgram\Services\BaiduTemplate; // 消息模板
use BaiduMiniProgram\Services\BaiduTemplateMessage; // 模板消息

// 获取 BaiduServiceClient 实例，此实例包含 HTTP Client，主要用于发送请求。
$serviceClient = $app->serviceClient();

// 创建 BaiduTemplate 实例，用于管理消息模板。
$template = new BaiduTemplate($serviceClient);
// 调用 $template 相关方法即可。

// 根据模板 ID，发送模板消息，可链式调用。
$data = (new BaiduTemplateMessage($templateId, $serviceClient))
    ->withKeywords([
        'keyword1' => 'foo',
        'keyword2' => 'bar',
    ])
    ->sendTo('小程序用户 Swan ID', 'Scene ID');
// $data 为发送结果，即接口响应的 `data` 字段。
```

### 支付

支付部分比较特殊，百度收银台是独立的聚合支付产品线，所以小程序接入稍显复杂，需要单独注册账号并认证。

首先，按照 [官方文档](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/access_process.md) 说明，入驻平台、创建服务等。

这里单独说明 [设置中心](http://dianshang.baidu.com/platform/developer/index.html#!/set) 内几处需要注意的地方。

1. 生成密钥可使用本 SDK 附带的一键脚本。

    ```bash
    bin/genrsa [密钥生成目录]
    ```

2. 开发者公钥保存后，需要稍等并刷新才会有平台公钥生成。

3. 平台公钥导出后并非 PEM 格式，无法被 OpenSSL 正确识别，需进行转换；同样可使用 SDK 附带的脚本。

    ```bash
    bin/wrap-key <单行密钥文本>
    ```

*TODO*

### 支付通知

在支付成功等场景，百度会主动发起通知请求到我方服务器。我方服务器需对请求签名进行验证，确保此请求来自百度服务器，且数据未被篡改。

同时，百度规定了响应格式，我方接口必须按照指定格式响应请求。

在本 SDK 内，可直接使用如下方式实现以上两点。

```php
$response = $payment->handleNotification(
    function ($parameters) {
        // 在这里编写业务逻辑，发生任何错误只需抛异常即可。
        // $parameters 是成功验证签名，并删除「签名」参数的请求参数数组。
        // 若业务逻辑成功处理，可返回一数组或对象，它将被直接填入响应 `data` 字段。
    },
    function (\Exception $exception) {
        // 在这里记录异常，例如发送到 Bugsnag 或 Sentry、记录至日志等。
        // 切勿输出任何内容，在回调通知请求内，你无法得知输出了啥。
    },
    $_POST // 此参数可忽略，默认即为 $_POST；通常用于非 PHP-FPM 等特殊场景（例如 Swoole）传入请求参数数组。
);

// 根据框架不同，可使用不同的方式输出 $response。
echo $response;
```

有点类似 JavaScript 的异步回调；在回调函数内，所有异常均会被妥善处理为指定格式响应，你需要关心的只有你的业务逻辑，并在第二个回调函数内记录一切发生的异常即可。

你也可以使用任意 [Callable](http://php.net/manual/zh/language.types.callable.php) 代替闭包。

### 深入

本 SDK 遵循「[PSR-7 HTTP Message](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md)」、HTTP 客户端基于「[HTTPlug](https://github.com/php-http/httplug)」,因此你可以任意定制 HTTP 客户端，只要兼容 PSR-7 即可。

[有什么好处？](https://wi1dcard.cn/documents/psr7-and-httplug/)

通常情况下，本 SDK 使用内置的 [`BaiduHttpClient`](src/Client/BaiduHttpClient.php) 为默认 HTTP 客户端，此客户端使用 CURL 驱动，代码摘自 [php-http/curl-client](https://github.com/php-http/curl-client/blob/master/src/Client.php)，经过修改后支持 PHP 5.4。

当然，你可以替换成自己喜欢的客户端，查看受支持的 [客户端列表](https://packagist.org/providers/php-http/client-implementation)。

例如替换为 `Guzzle 6.x`。

```bash
composer require guzzlehttp/guzzle:^6.0 # 安装 Guzzle，若已安装可跳过
composer require php-http/discovery # 此扩展包用于自动发现可用的 HTTP 客户端
composer require php-http/guzzle6-adapter # 安装适配器，适配 Guzzle + HTTPlug
```

或者，你也可以自行编写一个实现 [`Http\Client\HttpClient`](https://github.com/php-http/httplug/blob/master/src/HttpClient.php) 接口的客户端，然后在类构造函数内传入即可。

例如替换为 `YourHttpClient`。

```php
class YourHttpClient implements Http\Client\HttpClient
{
    public function sendRequest(Psr\Http\Message\RequestInterface $request) : Psr\Http\Message\ResponseInterface
    {
        // 发送兼容 PSR-7 RequestInterface 的请求
        // 返回兼容 PSR-7 ResponseInterface 的响应
    }
}

$app = new BaiduClient('App Key', 'App Secret', new YourHttpClient());

// 接下来，当调用 $app 内的方法、需要发送 HTTP 请求时，均会通过 YourHttpClient::sendRequest。
```

## 其它资源

- [百度智能小程序官方文档](https://smartprogram.baidu.com/docs/develop/tutorial/codedir/)
- [百度智能小程序官方支付文档](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/product_intro.md)
- [百度小程序开发资源汇总](https://github.com/quickappdev/awesome-smartapp)
- [微信小程序转百度小程序评估](./WECHAT-TO-BAIDU.md)

## 感想

在研究小程序支付部分时，居然发现其 [签名过程](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_2_base/sign_v2.md)、[SDK](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_3_business/moneycourt/settle.md) 等几乎与 [支付宝开放平台 SDK](https://docs.open.alipay.com/54/103419/) 一模一样。但从修改日期来看，支付宝是 2014 年，百度是 2016 年，且百度的代码相对规范些。

难不成... 是大佬被挖走了？

## 协议

MIT

欢迎 Issue / PR。

欢迎关注我们的产品。

[<img src="https://i.loli.net/2018/07/24/5b56dda76b2ba.png" width="30%" height="30%">](http://www.zjhejiang.com/)