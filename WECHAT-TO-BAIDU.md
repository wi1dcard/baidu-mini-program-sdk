# 微信小程序转百度小程序注意事项

以下为我公司产品计划支持百度小程序时进行的自查评估，仅供参考；如有遗漏或错误，欢迎 PR 补充。

## 一、前端部分

### 1) 总览

| 检查项       | 微信                                           | 百度                               | 备注                                                                             |
| ------------ | ---------------------------------------------- | ---------------------------------- | -------------------------------------------------------------------------------- |
| 文件后缀     | `wxml` / `wxss` / `js` / `json`                | `swan` / `css` / `js` / `json`     |
| 数据绑定     | `{{ xxx }}`                                    | 同微信                             | [参考链接](https://smartprogram.baidu.com/docs/develop/framework/view_data/)     |
| 布局控制结构 | 以 `wx:` 开头                                  | 以 `s-` 开头                       | 缺少 `s-key`                                                                     |
| 布局模板     | `<template ... data="{{exportData: myData}}">` | `<template ... data="{{myData}}">` | [参考链接](https://smartprogram.baidu.com/docs/develop/framework/view_template/) |
| 组件事件     | 以 `bind` 或 `catch` 开头                      | 同微信                             | [参考链接](https://smartprogram.baidu.com/docs/develop/framework/view_incident/) |
| JSON 文件    | -                                              | 同微信                             | [参考链接](https://smartprogram.baidu.com/docs/develop/tutorial/process_page/)   |
| API          | `wx.*`                                         | `swan.*`                           |
| 开发者工具   | -                                              | 类似微信                           |

以上，部分检查项没有进行具体对比；如有部分特殊事件、控制结构等百度不支持，则需要特殊处理。

**难点**：API 兼容库。

### 2) API 细节

| 检查项 | 微信                           | 百度                     | 备注                                                                                                                               |
| ------ | ------------------------------ | ------------------------ | ---------------------------------------------------------------------------------------------------------------------------------- |
| 登录   | code -> session_key -> decrypt | 流程同微信               | [参考链接](https://smartprogram.baidu.com/docs/develop/api/open_log/#login/)                                                       |
| 支付   | -                              | 独立产品体系，完全不一样 | [参考链接](https://dianshang.baidu.com/platform/doclist/index.html#!/doc/nuomiplus_1_guide/mini_program_cashier/access_process.md) |

...

**难点**：支付部分差距大。

## 二、后端部分

| 检查项       | 微信                   | 百度                                 | 备注                                                                                                 |
| ------------ | ---------------------- | ------------------------------------ | ---------------------------------------------------------------------------------------------------- |
| 用户标识     | `open_id` + `union_id` | `open_id` + `swan_id`                |
| 模版消息     | 无场景区分             | 区分场景 `表单` / `支付`，类似支付宝 | `access_token` 需单独获取，[参考链接](https://smartprogram.baidu.com/docs/develop/server/power_exp/) |
| 小程序二维码 | 调用接口生成           | 需在后台配置规则后直接生成           | [参考链接](https://smartprogram.baidu.com/docs/introduction/scancode/)                               |
| 分销佣金     | 支持自动打款           | 无此接口                             |

**难点**：支付部分差距大，支付接口 SDK 需进行简化集成，其它接口百度不提供 SDK，需自行封装。