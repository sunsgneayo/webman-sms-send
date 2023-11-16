<?php
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