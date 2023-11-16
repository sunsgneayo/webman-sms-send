
<div align="center" style="border-radius: 50px">
    <img width="260px"  src="https://cdn.nine1120.cn/logo-i.png" alt="sunsgne">
</div>

**<p align="center">sunsgne/webman-sms-register</p>**

**<p align="center">🐬 Webman configuration system covering global SMS registration verification codes 🐬</p>**

<div align="center">

[![Latest Stable Version](http://poser.pugx.org/sunsgne/webman-sms-register/v)](https://packagist.org/packages/sunsgne/webman-sms-register)
[![Total Downloads](http://poser.pugx.org/sunsgne/webman-sms-register/downloads)](https://packagist.org/packages/sunsgne/webman-sms-register)
[![Latest Unstable Version](http://poser.pugx.org/sunsgne/webman-sms-register/v/unstable)](https://packagist.org/packages/sunsgne/webman-sms-register)
[![License](http://poser.pugx.org/sunsgne/webman-sms-register/license)](https://packagist.org/packages/sunsgne/webman-sms-register)
[![PHP Version Require](http://poser.pugx.org/sunsgne/webman-sms-register/require/php)](https://packagist.org/packages/sunsgne/webman-sms-register)

</div>

## Webman SMS Register 

### 功能特性

- [x] 简单快速的API，各模块可配置切支持扩展的引入
- [x] 内置图形验证码，支持数字/字母/数学公式等验证方式
- [x] 集成短信SMS发送请求
- [x] 独立可移植的数据系统
- [ ] 完备短信模板管理，语言配置等后台管理API和面板

### 环境
- PHP >= 8.1.0
- workerman/webman-framework:"^1.4.3"
- illuminate/database
- illuminate/redis

### 开始
#### 安装/引入
```shell
composer require sunsgen/webman-sms-register
```
#### 初始化数据表
⚠️：请务必使用`illuminate/database`也就是`laravel`的数据库`orm`, 可参照[webman官方文档](https://www.workerman.net/doc/webman/db/tutorial.html);
```shell
./webman init-table
or 
php webman init-table
```
#### 为国家/地区导入初始数据
```shell
./webman sync-country-data
or 
php webman sync-country-data
```


### 图片验证码
- [tinywan/captcha](https://www.workerman.net/plugin/33)

### 短信服务（SMS）
- [x] 腾讯
- [ ] 阿里

### API
#### 国家/地区区域码列表
```php
use Sunsgne\WebmanSmsRegister\App;
App::GetCountryCodeList()
```
#### 图片验证码
```php
use Sunsgne\WebmanSmsRegister\App;
App::GetBase64Captcha()
```
#### 图片验证码验证
```php
use Sunsgne\WebmanSmsRegister\App;
App::VerifyCaptchaCode()
```
#### 发送手机验证码
```php
use Sunsgne\WebmanSmsRegister\App;
App::SendSmsCodeByTencent()
```

#### 验证手机
```php
use Sunsgne\WebmanSmsRegister\App;
App::VerifyMobileCode()
```


#### 写入用户数据（建议异步调用）
```php
use Sunsgne\WebmanSmsRegister\App;
App::saveMobileUsers()
```
