<?php

namespace Sunsgne\WebmanSmsSend\Sms;

use Sunsgne\WebmanSmsSend\Exception\SmsClientException;
use Sunsgne\WebmanSmsSend\Sms\Provider\TencentSmsProvider;

/**
 * @Time 2023/11/13 16:49
 * @author sunsgne
 * @property  TencentSmsProvider $tencent 腾讯云发送验证码
 */
class SmsClient
{


    /** @var array|string[] */
    protected array $alias = [
        'tencent' => TencentSmsProvider::class
    ];

    /** @var array */
    protected array $providers = [];

    /** @var array */
    protected array $configs = [];

    /**
     * 初始化配置
     * @param array|null $configs
     */
    public function __construct(?array $configs = null)
    {
        $this->configs = $configs ?? [];
    }

    /**
     * 调用提供者
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!isset($name) || !isset($this->alias[$name])) {
            throw new SmsClientException("{$name} is invalid.");
        }

        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }
        $class = $this->alias[$name];
        return $this->providers[$name] = $this->configs ?
            new $class($this, $this->configs) :
            new $class($this);
    }
}