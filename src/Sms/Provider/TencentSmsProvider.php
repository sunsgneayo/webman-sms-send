<?php
declare(strict_types=1);

namespace Sunsgne\WebmanSmsSend\Sms\Provider;

use Sunsgne\WebmanSmsSend\Exception\SmsClientException;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\SmsClient;

/**
 * @Time 2023/11/13 16:36
 * @author sunsgne
 */
class TencentSmsProvider
{

    /** @var Credential 签名生成器 */
    protected Credential $credential;

    /** @var SendSmsRequest 短信发送请求 */
    protected SendSmsRequest $request;

    /** 服务id */
    /** @var string|mixed */
    protected string $secretId;

    /** @var string|mixed 服务key */
    protected string $secretKey;

    /** @var string|mixed SDK的appid */
    protected string $sdkAppId;

    /** @var string|mixed 短信签名 */
    protected string $signName;

    /** @var string 模板id */
    protected string $templateId = '1430565';

    /** @var string 服务器大区 */
    protected string $region = 'ap-guangzhou';

    /**
     * 发送短信
     * @author yanglong
     * @date 2022年8月29日11:40:39
     * @example 用法(常规用法)：
     * $sms=new TencentSms();
     * $sms->setTemplate('');
     * $sms->setContent(['9527']);
     * $sms->sendTo([$phone1,$phone2]);
     * $response=$sms->send();
     * @example 用法（链式操作）：
     * $sms=new TencentSms();
     * $response=$sms->setTemplate('')->setContent(['9527'])->sendTo([$phone1,$phone2])->send();
     */
    public function __construct()
    {
        $config           = config('plugin.sunsgne.webman-sms-register.sms.tencent');
        if (empty($config)){
            throw new SmsClientException('未配置短信平台参数');
        }

        $this->secretId   = $config['secretId'];
        $this->secretKey  = $config['secretKey'];
        $this->sdkAppId   = $config['sdkAppId'];
        $this->signName   = $config['signName'];
        $this->region     = $config['region'] ?? $this->region;
        $credential       = new Credential($this->secretId, $this->secretKey);
        $this->credential = $credential;
        $request          = new SendSmsRequest();
        $request->setSmsSdkAppId($this->sdkAppId);
        $this->request = $request;
        return $this;
    }

    /**
     * 接收短信电话号码组
     * @param array $sendTo =['+8613983436511','+8613983436511',...]
     * @return object
     */
    public function sendTo(array $sendTo): object
    {
        $this->request->setPhoneNumberSet($sendTo);
        return $this;
    }

    /**
     * 设置模板id
     * @param string $templateId
     * @return object
     */
    public function setTemplate(string $templateId ): object
    {
        if ($templateId) {
            $this->templateId = $templateId;
        }
        $this->request->setTemplateId($this->templateId);
        return $this;
    }

    /**
     * 设置短信模板中的变量
     * @param array $content =['value1','value2',...]
     * @return object
     */
    public function setContent(array $content): object
    {
        foreach ($content as &$v) {
            $v = (string)$v;
        }
        $this->request->setTemplateParamSet(array_values($content));
        return $this;
    }

    /**
     * 设置标题
     * @param string $signName
     * @return object
     */
    public function setSignName(string $signName): object
    {
        $this->request->setSignName($signName);
        return $this;
    }

    /**
     * 发送短信
     * @return array
     */
    public function send(): array
    {
        try {
            $msgClient = new SmsClient($this->credential, $this->region);
            $content   = $msgClient->SendSms($this->request);
            return json_decode(json_encode($content), true);
        } catch (TencentCloudSDKException $exception) {
            throw new TencentMsgException($exception->getMessage());
        }
    }
}
