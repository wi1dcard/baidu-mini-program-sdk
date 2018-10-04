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

### 准备

```bash
composer require wi1dcard/baidu-mini-program-sdk:dev-master
```

### 登录

### 解密

### 模版消息

### 支付

## 其它资源

- [百度小程序官方文档](https://smartprogram.baidu.com/docs/develop/tutorial/codedir/)
- [百度小程序开发资源汇总](https://github.com/quickappdev/awesome-smartapp)

## 感想

在研究小程序支付部分时，居然发现其 [签名过程](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_2_base/sign_v2.md)、[SDK](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_3_business/moneycourt/settle.md) 等几乎与 [支付宝开放平台 SDK](https://docs.open.alipay.com/54/103419/) 一模一样。但从修改日期来看，支付宝是 2014 年，百度是 2016 年，且百度的代码相对规范些。

难不成... 是大佬被挖走了？

## 协议

MIT

欢迎 Issue / PR。