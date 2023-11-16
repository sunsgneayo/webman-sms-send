<?php
declare(strict_types=1);
namespace Sunsgne\WebmanSmsRegister\Service;

use Sunsgne\WebmanSmsRegister\Sms\SmsClient;

/**
 * @Time 2023/11/13 17:05
 * @author sunsgne
 */
class AppService
{

    /** @var SmsClient|null SMS客户端 */
    protected ?SmsClient $smsClient = null;

    public function __construct(){
        $this->smsClient = new  SmsClient();
    }

    /**
     * 使用腾讯云发送手机验证码
     * @param string $TemplateId 模板ID  示例值：1245
     * @param string $mobileNumber 手机号码 示例值：+8615998908728
     * @param string $content 发送模板变量内容 示例值： 5666 （校验码）
     * @param string $signName 标识
     * @return array
     * @Time 2023/11/13 17:06
     * @author sunsgne
     */
    public function SendSmsCodeByTencent(
        string $TemplateId , string $mobileNumber ,
        string $content , string $signName
    ): array
    {
        return $this->smsClient->tencent
            ->setSignName($signName)
            ->setTemplate($TemplateId)
            ->setContent([$content])
            ->sendTo([$mobileNumber])
            ->send();
    }
}