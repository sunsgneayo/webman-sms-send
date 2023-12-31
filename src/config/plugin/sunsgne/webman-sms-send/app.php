<?php
return [
    'enable' => true,

    'sms'     => [
        # 是否发送短信
        'sendSms'            => true,
        # 验证码长度
        'length'             => 4,
        # 根据以下字符生成验证码
        'rule'               => '0123456789',
        # 默认的短信过期时间
        'expiredTime'        => 5 * 60,
        # 是否使用默认模板
        'useDefaultTemp'     => true,
        # 默认的发送模板ID
        'defaultTempId'      => 1534804,
        # 默认的地区码
        'defaultCountryCode' => '86',

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