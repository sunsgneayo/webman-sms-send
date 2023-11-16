<?php
declare(strict_types=1);

namespace Sunsgne\WebmanSmsRegister;

use Exception;
use RedisException;
use Sunsgne\WebmanSmsRegister\Exception\CaptchaException;
use Sunsgne\WebmanSmsRegister\Exception\SmsAppException;
use Sunsgne\WebmanSmsRegister\Model\CountryMobile;
use Sunsgne\WebmanSmsRegister\Model\MobileUsers;
use Sunsgne\WebmanSmsRegister\Model\SmsSendLog;
use Sunsgne\WebmanSmsRegister\Model\SmsTemplate;
use Sunsgne\WebmanSmsRegister\Service\AppService;
use support\Container;
use support\RedisScope;
use Tinywan\Captcha\Captcha;

/**
 * @Time 2023/11/13 16:57
 * @author sunsgne
 */
class App
{

    /**
     * 默认的短信验证码过期时间
     * @var int
     */
    protected static int $_expiredTime = 5 * 60;


    /**
     * @param string $mobileNum 手机号码
     * @param string $countryCode 国家编号
     * @param string $appPkgName
     * @param string $clientIp
     * @param string $language
     * @param string $scenes
     * @return array
     * @throws RedisException
     * @Time 2023/11/16 14:05
     * @author sunsgne
     */
    public static function SendSmsCodeByTencent(
        string $mobileNum,
        string $countryCode,
        string $clientIp,
        string $appPkgName = '*',
        string $language = '*',
        string $scenes = 'public'
    ): array
    {

        $mobile = ('+' . $countryCode . $mobileNum);
        if (!self::verifyClientIp($clientIp)) {
            throw new SmsAppException('当前IP超过最大发送次数');
        }

        if (RedisScope::list()->get("SMS:$scenes:$mobile")) {
            throw new SmsAppException('请不要重复发送手机验证码', 400);
        }


        /** @var AppService $appService */
        $appService = Container::get(AppService::class);

        # 根据语言，国家地区编号，包名去查找对应的模板ID
        $temp = SmsTemplate::firstByWhere([
            'country_mobile_code' => $countryCode,
            'language'            => $language,
            'status'              => 1,
            'app_package_name'    => $appPkgName,
            'sms_type'            => $scenes
        ], ['template_id', 'template_name', 'sms_expired_time', 'sms_sign']);

        if (empty($tempId = $temp['template_id'] ?? null)) {
            if (!config('plugin.sunsgne.webman-sms-register.app.sms.useDefaultTemp', false)) {
                throw new SmsAppException('未找到短信模板ID', 404);
            }
            if (!($tempId = config('plugin.sunsgne.webman-sms-register.app.sms.defaultTempId'))) {
                throw new SmsAppException('未配置默认的短信模板ID', 404);
            }
        }

        $result = $appService->SendSmsCodeByTencent(
            (string)$tempId,
            $mobile,
            $vCode = self::generateCode(),
            $temp['sms_sign'] ?? config('plugin.sunsgne.webman-sms-register.sms.tencent.signName')
        );

        RedisScope::list()->setEx(
            "SMS:$scenes:$mobile",
            $temp['sms_expired_time'] ?? self::$_expiredTime,
            $vCode
        );
        self::saveSmsSendLog(
            result: $result, countryCode: (int)$countryCode,
            scenes: $scenes, mobileNum: (int)$mobileNum
        );
        self::syncIpSendNum($clientIp);
        return $result;

    }

    /**
     * 验证IP
     * @param string $clientIp
     * @return bool
     * @Time 2023/11/16 14:51
     * @author sunsgne
     */
    protected static function verifyClientIp(string $clientIp): bool
    {
        # 不验证IP
        if (!config('plugin.sunsgne.webman-sms-register.app.limitIp.enable', false)) {
            return true;
        }
        $maxNum = config('plugin.sunsgne.webman-sms-register.app.limitIp.maxSendNum', 10);

        $useNum = RedisScope::list()->get("SMS:$clientIp") ?? 0;
        if ($useNum >= $maxNum) {
            return false;
        }
        return true;
    }

    /**
     * 同步Ip次数验证
     * @param string $clientIp
     * @return void
     * @throws RedisException
     * @Time 2023/11/16 14:44
     * @author sunsgne
     */
    protected static function syncIpSendNum(string $clientIp): void
    {
        if (!config('plugin.sunsgne.webman-sms-register.app.limitIp.enable', false)) {
            return;
        }
        $cacheKey = "SMS:$clientIp";
        if (empty(RedisScope::list()->get($cacheKey))) {
            RedisScope::list()->setex(
                $cacheKey,
                config('plugin.sunsgne.webman-sms-register.app.limitIp.duration', 24 * 60 * 60),
                1
            );
        } else {
            RedisScope::list()->incr($cacheKey);
        }

    }

    /**
     * 校验国家编号是否存在与手机号正则验证
     *
     * @param string $countryCode 国家编号 +86
     * @param string $mobileNumber 手机号码 13212312312
     * @return bool
     * @Time 2023/11/16 14:28
     * @author sunsgne
     */
    public static function VerifyLegalMobile(string $countryCode, string $mobileNumber): bool
    {
        //检查country是否存在
        $countryMobile = CountryMobile::query()
            ->where(['country_mobile_code' => (int)$countryCode])
            ->select('id', 'regex')
            ->first();

        # 不存在国家区号
        if (!$countryMobile) {
            return false;
        }

        # 手机号长度小于4位
        if (strlen($mobileNumber) < 4) {
            return false;
        }

        # 未配置手机验证正则
        if (empty($countryMobile['regex'])) {
            return true;
        }

        # 正则验证不通过
        if (!preg_match($countryMobile['regex'], $mobileNumber)) {
            return false;
        }

        # 正则验证通过
        return true;
    }


    /**
     * 根据配置规则生成验证码
     * @return string
     * @Time 2023/11/13 18:44
     * @author sunsgne
     */
    private static function generateCode(): string
    {
        # 打乱字符串顺序
        $shuffledString = str_shuffle(config('plugin.sunsgne.webman-sms-register.app.sms.rule', '0123456789'));

        # 截取前4位作为验证码
        return substr($shuffledString, 0, config('plugin.sunsgne.webman-sms-register.app.sms.length', 4));
    }


    /**
     * 返回国家地区/编码、国旗、手机区号等列表
     *
     * @return array
     * @Time 2023/11/14 13:12
     * @author sunsgne
     */
    public static function GetCountryCodeList(): array
    {
        $data = CountryMobile::query()->where(['status' => 1])->get();
        return $data->isNotEmpty() ? $data->toArray() : config('plugin.sunsgne.webman-sms-register.country', []);
    }


    /**
     * 返回国家编号作为key的列表，并可根据旗子前缀地址拼接
     * @param string|null $flag
     * @return array
     * @Time 2023/11/16 15:50
     * @author sunsgne
     */
    public static function GetCountryCodeAsKeyList(?string $flag = ''): array
    {
        $data   = CountryMobile::query()->where(['status' => 1])->get();
        $list   = $data->isNotEmpty() ? $data->toArray() : config('plugin.sunsgne.webman-sms-register.country', []);
        $result = [];
        foreach ($list as $value) {
            $result[$value['country_code']] = [
                'country_name'        => json_decode($value['country_name']),
                'country_name_zh'     => $value['country_name_zh'] ?? '',
                'country_mobile_code' => $value['country_mobile_code'] ?? '',
                'regex'               => $value['regex'] ?? '',
                'national_flag'       => empty($value['national_flag']) ? '' : ($flag . $value['national_flag']),
            ];
        }

        return $result;
    }

    /**
     * 验证手机验证码
     *
     * @param string $countryCode 国家地区编码
     * @param string $mobileNum 手机号码
     * @param string $scenes 验证场景
     * @param string $vCode 验证码
     * @return void
     * @Time 2023/11/14 11:45
     * @author sunsgne
     */
    public static function VerifyMobileCode(
        string $countryCode, string $mobileNum,
        string $scenes, string $vCode
    ): void
    {
        $mobile = ('+' . $countryCode . $mobileNum);

        if (empty($result = RedisScope::list()->get("SMS:$scenes:$mobile"))) {
            throw new SmsAppException('手机号码验证失败', 404);
        }
        if ($result != $vCode) {
            throw new SmsAppException('手机号码验证失败', 400);
        }
        RedisScope::list()->del("SMS:$scenes:$mobile");

    }


    /**
     * 保存手机短信发送记录
     *
     * @param array $result
     * @param int $countryCode
     * @param string $scenes
     * @param int $mobileNum
     * @param string|null $service
     * @return void
     * @Time 2023/11/14 13:29
     * @author sunsgne
     */
    protected static function saveSmsSendLog(
        array   $result, int $countryCode,
        string  $scenes, int $mobileNum,
        ?string $service = 'Tencent'
    ): void
    {
        SmsSendLog::insert([
            'country_mobile_code' => $countryCode,
            'mobile'              => $mobileNum,
            'scenes'              => $scenes,
            'create_time'         => time(),
            'update_time'         => time(),
            'sms_service'         => $service,
            'sms_response'        => json_encode($result)
        ]);
    }


    /**
     * 保存手机号注册的用户
     * @param int $userId 用户ID
     * @param int $countryCode 国家地区码
     * @param int $mobileNum 手机号码
     * @return void
     * @Time 2023/11/14 11:43
     * @author sunsgne
     */
    public static function saveMobileUsers(int $userId, int $countryCode, int $mobileNum): void
    {
        if (!MobileUsers::query()->where(['user_id' => $userId])->exists()) {
            MobileUsers::query()->insert([
                'user_id'             => $userId,
                'country_mobile_code' => $countryCode,
                'mobile'              => (string)$mobileNum,
                'create_time'         => time(),
                'update_time'         => time(),
            ]);
        }
    }

}
