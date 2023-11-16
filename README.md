
<div align="center" style="border-radius: 50px">
    <img width="260px"  src="https://cdn.nine1120.cn/logo-i.png" alt="sunsgne">
</div>

**<p align="center">sunsgne/webman-sms-send</p>**

**<p align="center">🐬 Webman configuration system covering global SMS registration verification codes 🐬</p>**

<div align="center">

[![Latest Stable Version](http://poser.pugx.org/sunsgne/webman-sms-send/v)](https://packagist.org/packages/sunsgne/webman-sms-send)
[![Total Downloads](http://poser.pugx.org/sunsgne/webman-sms-send/downloads)](https://packagist.org/packages/sunsgne/webman-sms-send)
[![Latest Unstable Version](http://poser.pugx.org/sunsgne/webman-sms-send/v/unstable)](https://packagist.org/packages/sunsgne/webman-sms-send)
[![License](http://poser.pugx.org/sunsgne/webman-sms-send/license)](https://packagist.org/packages/sunsgne/webman-sms-send)
[![PHP Version Require](http://poser.pugx.org/sunsgne/webman-sms-send/require/php)](https://packagist.org/packages/sunsgne/webman-sms-send)

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
- workerman/webman-framework
- illuminate/database
- illuminate/redis

### 开始
#### 安装/引入
```shell
composer require sunsgne/webman-sms-register
```
#### 初始化数据表
⚠️：请务必使用`illuminate/database`也就是`laravel`的数据库`orm`, 可参照[webman官方文档](https://www.workerman.net/doc/webman/db/tutorial.html);
```shell
./webman wsr-init-table
or 
php webman wsr-init-table
```
#### 为国家/地区导入初始数据
```shell
./webman sync-country-data
or 
php webman sync-country-data
```

#### 配置SMS云短信
以下是Tencent的短信参数示例
```php
return [
    # 腾讯云-短信发送配置
    'tencent' => [
        'secretId'  => 'AKIDNaXEeoiLhma7NM4WhaDZeutb3E8l9G6e',
        'secretKey' => 'JL6anlSs1tUorMaXDcldEYbNt86nDMEq',
        'sdkAppId'  => '1400696413',
        'signName'  => 'BeiWorld',  //默认的短信签名
        'region'    => 'ap-guangzhou' // 默认的发送区域
    ]
];
```
文件位置：**/config/plugin/sunsgne/webman-sms-register/sms.php**
#### 配置默认短信发送
```php
return [
    'enable' => true,
    'sms'     => [
        'length'         => 4,
        # 根据以下字符生成验证码
        'rule'           => '0123456789',
        # 是否使用默认模板
        'useDefaultTemp' => true,
        # 默认的发送模板ID
        'defaultTempId'  => 1534804
    ],
    'limitIp' => [
        # 是否开启ip发送次数验证
        'enable'     => true,
        # 验证周期（24小时不能超过maxSendNum）
        'duration'   => 60 * 60 * 24,
        # 周期内最大次数
        'maxSendNum' => 10
    ]
];
```

*** 

### SQL 相关表结构
- **country_code** （国际手机区域码配置表）
```sql
  CREATE TABLE `country_mobile` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `country_name_zh` varchar(255) NOT NULL COMMENT '国家中文名称（中国）',
  `country_name` json DEFAULT NULL COMMENT '国家名称;{"zh":"中国","en":"CHINA"}',
  `country_code` varchar(255) NOT NULL COMMENT '国家代号',
  `country_mobile_code` int NOT NULL COMMENT '国家/地区手机码',
  `regex` varchar(255) DEFAULT NULL COMMENT '手机号规则（正则表达式）',
  `national_flag` varchar(255) DEFAULT NULL COMMENT '国旗标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：异常；1：正常；默认1）',
  `create_time` int DEFAULT NULL COMMENT '创建时间',
  `update_time` int DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `country_code_index` (`country_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COMMENT='国际手机区域码配置表';
```
- **sms_template** (短信模板配置表)
```sql
CREATE TABLE `sms_template` (
  `id` int NOT NULL AUTO_INCREMENT,
  `country_mobile_id` int NOT NULL COMMENT '关联country_mobile表中主键ID',
  `sms_service` varchar(255) NOT NULL COMMENT '短信服务商（tencent/alibaba）',
  `template_name` varchar(255) DEFAULT NULL COMMENT '短信模板名称',
  `template_id` int NOT NULL COMMENT '短信模板ID',
  `sms_type` varchar(255) DEFAULT NULL COMMENT '短信应用场景(register:注册；....)',
  `country_mobile_code` varchar(32) NOT NULL COMMENT '国家/地区手机码',
  `language` varchar(32) DEFAULT NULL COMMENT '短信语言（中文、英文）示例值:zh',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：异常；1：正常；默认1）',
  `sms_content` varchar(255) DEFAULT NULL COMMENT '短信内容（验证码{1}，仅用于绑定手机，请勿告知他人，如有疑问请联系客服。）',
  `sms_sign` varchar(255) DEFAULT NULL COMMENT '短信签名（bei）',
  `app_package_name` varchar(128) DEFAULT NULL COMMENT '应用包名',
  `sms_expired_time` int DEFAULT NULL COMMENT '短信过期时长（单位：秒）',
  `create_time` int DEFAULT NULL COMMENT '创建时间',
  `update_time` int DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `c_s_t_index` (`country_mobile_id`,`sms_service`,`template_id`) USING BTREE,
  KEY `s_c_l_a_index` (`sms_type`,`country_mobile_code`,`language`,`app_package_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COMMENT='国家/地区短信类型模板配置表';
```

- **mobile_users** (手机号用户表)
```sql
CREATE TABLE `mobile_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL COMMENT '用户ID(与业务关联的唯一ID)',
  `country_mobile_code` int NOT NULL COMMENT '国家/地区手机区域码（86）',
  `mobile` varchar(64) NOT NULL COMMENT '手机号码(可加密)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：异常；1：正常；默认1）',
  `create_time` int DEFAULT NULL COMMENT '创建时间',
  `update_time` int DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_index` (`user_id`) USING BTREE COMMENT '用户唯一ID',
  KEY `mobile_index` (`mobile`) USING BTREE COMMENT '手机号'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='手机号用户表（webman-sms-register）';
```

- **sms_send_log** (sms短信发送记录表)
```sql
CREATE TABLE `sms_send_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `country_mobile_code` int NOT NULL,
  `mobile` varchar(32) NOT NULL,
  `scenes` varchar(64) DEFAULT NULL COMMENT '发送场景（register:注册....由客户端自定义）',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1:正常；0：异常）',
  `sms_service` varchar(64) DEFAULT NULL COMMENT '短信发送服务方',
  `sms_response` json DEFAULT NULL COMMENT '发送请求之后的响应',
  `create_time` int DEFAULT NULL COMMENT '创建时间',
  `update_time` int DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `c_m_s_index` (`country_mobile_code`,`mobile`,`scenes`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

*** 


### 短信服务（SMS）
- [x] 腾讯
- [ ] 阿里

### API
#### 国家/地区区域码列表
```php
use Sunsgne\WebmanSmsRegister\App;
App::GetCountryCodeList()
```
#### 国家/地区区域码列表(以国家编号作为KEY)
```php
use Sunsgne\WebmanSmsRegister\App;
App::GetCountryCodeList()
```
返回的结构示例：
```json5
{
  "AD": {
    "country_mobile_code": 376,
    "country_name": {
      "en": "Andorra",
      "tw": "安道爾共和國",
      "zh": "安道尔共和国"
    },
    "country_name_zh": "安道尔共和国",
    "national_flag": "AD.png",
    "regex": ""
  }
}
```


#### 验证手机和国家编码合法
```php
$status = App::VerifyLegalMobile('86' , '3255214');//bool
```


#### 发送手机验证码
```php
use Sunsgne\WebmanSmsRegister\App;
try {
    App::SendSmsCodeByTencent(
        mobileNum: '15998908728',
        countryCode: '86',
        clientIp: $request->getRealIp(false)
    );
} catch (\RedisException|SmsAppException $e) {
    dump($e->getMessage());
}
```

#### 验证手机短信验证码
```php
use Sunsgne\WebmanSmsRegister\App;
try {
    App::VerifyMobileCode(
        countryCode: '86',  // 国家地区编码
        mobileNum: '13012345678', //手机号码
        scenes: 'register', //发送场景
        vCode: '2154' // 验证码
    );
} catch (RedisException|SmsAppException $e) {
    //验证失败
}
```
