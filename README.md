Baidu Smart Mini-Program SDK for PHP
==========

<img src="https://smartprogram.baidu.com/docs/img/logo.png" height="30px">

🐾 百度小程序第三方 PHP SDK，助力智能小程序开发。

[![Build Status](https://travis-ci.org/wi1dcard/baidu-mini-program-sdk.svg?branch=master)](https://travis-ci.org/wi1dcard/baidu-mini-program-sdk)
[![Coverage Status](https://coveralls.io/repos/github/wi1dcard/baidu-mini-program-sdk/badge.svg)](https://coveralls.io/github/wi1dcard/baidu-mini-program-sdk)
[![StyleCI](https://github.styleci.io/repos/151553953/shield?branch=master)](https://github.styleci.io/repos/151553953)
[![Packagist](https://img.shields.io/packagist/v/wi1dcard/baidu-mini-program-sdk.svg)](https://packagist.org/packages/wi1dcard/baidu-mini-program-sdk)

❤️ 本项目 [GitHub](https://github.com/wi1dcard/baidu-mini-program-sdk-php) / [Gitee(码云)](https://gitee.com/wi1dcard/baidu-mini-program-sdk-php)。

🎉 [支付宝开放平台第三方 PHP SDK](https://github.com/wi1dcard/alipay-sdk-php)。

* **目录**
    * [主要目的](#主要目的)
    * [如何使用](#如何使用)
        * [准备](#准备)
        * [登录](#登录)
        * [解密](#解密)
        * [模版消息](#模版消息)
        * [支付](#支付)
    * [其它资源](#其它资源)
    * [感想](#感想)
    * [协议](#协议)

## 主要目的

集成以下功能：

1. 登录接口
2. 解密接口
3. 模板消息接口
4. 支付 SDK

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

TODO

### 支付

支付部分比较特殊，百度收银台是独立的聚合支付产品线，所以小程序接入稍显复杂，需要单独注册账号并认证。

1. 按照 [官方文档](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/access_process.md) 说明，入驻平台、创建服务等。
2. TODO

## 其它资源

- [百度小程序官方文档](https://smartprogram.baidu.com/docs/develop/tutorial/codedir/)
- [百度小程序开发资源汇总](https://github.com/quickappdev/awesome-smartapp)
- [百度小程序接入支付](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/product_intro.md)

## 感想

在研究小程序支付部分时，居然发现其 [签名过程](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_2_base/sign_v2.md)、[SDK](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_3_business/moneycourt/settle.md) 等几乎与 [支付宝开放平台 SDK](https://docs.open.alipay.com/54/103419/) 一模一样。但从修改日期来看，支付宝是 2014 年，百度是 2016 年，且百度的代码相对规范些。

难不成... 是大佬被挖走了？

## 协议

MIT

欢迎 Issue / PR。