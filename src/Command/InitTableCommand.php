<?php
declare(strict_types=1);

namespace Sunsgne\WebmanSmsSend\Command;
use Exception;
use support\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Sunsgne\WebmanSmsSend\Model\MobileUserLoginLog;
use support\Container;
/**
 * @Time 2023/11/8 17:07
 * @author sunsgne
 */
class InitTableCommand extends Command
{

    protected static $defaultName = 'wsr-init-table';
    protected static $defaultDescription = '初始化手机短信注册相关表';

    protected static array $_SQL = [
        # 手机号用户表
        'mobile_users' => <<<doc
CREATE TABLE `mobile_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID(与业务关联的唯一ID)',
  `country_mobile_code` int(11) NOT NULL COMMENT '国家/地区手机区域码（86）',
  `mobile` varchar(64) NOT NULL COMMENT '手机号码(可加密)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：异常；1：正常；默认1）',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_index` (`user_id`) USING BTREE COMMENT '用户唯一ID',
  KEY `mobile_index` (`mobile`) USING BTREE COMMENT '手机号'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci  COMMENT='手机号用户表（webman-sms-register）';
doc,
        # 国际手机区域码配置表
        'country_mobile' => <<<doc
CREATE TABLE `country_mobile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_name_zh` varchar(255) NOT NULL COMMENT '国家中文名称（中国）',
  `country_name` json DEFAULT NULL COMMENT '国家名称;{"zh":"中国","en":"CHINA"}',
  `country_code` varchar(255) NOT NULL COMMENT '国家代号',
  `country_mobile_code` int(11) NOT NULL COMMENT '国家/地区手机码',
  `regex` varchar(255) DEFAULT NULL COMMENT '手机号规则（正则表达式）',
  `national_flag` varchar(255) DEFAULT NULL COMMENT '国旗标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：异常；1：正常；默认1）',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `country_code_index` (`country_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci  COMMENT='国际手机区域码配置表';
doc,
        # 国家/地区短信类型模板配置表
        'sms_template' => <<<doc
CREATE TABLE `sms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_mobile_id` int(11) NOT NULL COMMENT '关联country_mobile表中主键ID',
  `sms_service` varchar(255) NOT NULL COMMENT '短信服务商（tencent/alibaba）',
  `template_name` varchar(255) DEFAULT NULL COMMENT '短信模板名称',
  `template_id` int(11) NOT NULL COMMENT '短信模板ID',
  `sms_type` varchar(255) DEFAULT NULL COMMENT '短信应用场景(register:注册；....)',
  `country_mobile_code` varchar(32) NOT NULL COMMENT '国家/地区手机码',
  `language` varchar(32) DEFAULT NULL COMMENT '短信语言（中文、英文）示例值:zh',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（0：异常；1：正常；默认1）',
  `sms_content` varchar(255) DEFAULT NULL COMMENT '短信内容（验证码{1}，仅用于绑定手机，请勿告知他人，如有疑问请联系客服。）',
  `sms_sign` varchar(255) DEFAULT NULL COMMENT '短信签名（bei）',
  `app_package_name` varchar(128) DEFAULT NULL COMMENT '应用包名',
  `sms_expired_time` int(11) DEFAULT NULL COMMENT '短信过期时长（单位：秒）',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `c_s_t_index` (`country_mobile_id`,`sms_service`,`template_id`) USING BTREE,
  KEY `s_c_l_a_index` (`sms_type`,`country_mobile_code`,`language`,`app_package_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci  COMMENT='国家/地区短信类型模板配置表';
doc,

        # 国家/地区短信类型模板配置表
        'sms_send_log' => <<<doc
CREATE TABLE `sms_send_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `country_mobile_code` int NOT NULL,
  `mobile` varchar(32) NOT NULL,
  `scenes` varchar(64) DEFAULT NULL COMMENT '发送场景（register:注册....由客户端自定义）',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1:正常；0：异常）',
  `sms_service` varchar(64) DEFAULT NULL COMMENT '短信发送服务方',
  `sms_response` json DEFAULT NULL COMMENT '发送请求之后的响应',
  `client_ip` varchar(32) DEFAULT NULL COMMENT '客户端IP',
  `code` varchar(16) DEFAULT NULL COMMENT '验证码',
  `create_time` int DEFAULT NULL COMMENT '创建时间',
  `update_time` int DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `c_m_s_index` (`country_mobile_code`,`mobile`,`scenes`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='短信发送记录表';
doc,

    ];

    /**
     * @var InputInterface|null
     */
    protected ?InputInterface $input = null;

    /**
     * @var null|SymfonyStyle
     */
    protected ?OutputInterface $output = null;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionInterface
     * @Time 2023/11/10 18:23
     * @author sunsgne
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = new SymfonyStyle($input, $output);

        return parent::run($this->input = $input, $this->output);
    }


    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('table-name', InputArgument::OPTIONAL, '表名');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('table-name');
        if (empty($name)){
            # 執行全部SQL
            foreach (self::$_SQL as $tableName => $sql) {
                try {
                    $this->executeSQL($sql, $tableName);
                    $output->write("table '$tableName' creation completed" , true);

                } catch (Exception $e) {
                    $output->write($e->getMessage() , true);
                    return self::FAILURE;
                }
            }
            return self::SUCCESS;
        }
        if (isset(self::$_SQL[$name])){
            # 執行單個SQL
            try {
                $this->executeSQL(self::$_SQL[$name], $name);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
                return self::FAILURE;
            }
            $output->writeln("table '$name' creation completed");
            return self::SUCCESS;
        }

        $output->writeln(" [$name] table-name not found");
        return self::FAILURE;
    }




    /**
     * 执行SQL语句
     * @param string $sql
     * @param string $tableName
     * @throws Exception
     */
    private function executeSQL(string $sql , string $tableName): void
    {
        if (Db::schema()->hasTable($tableName)) {
            throw new Exception("Table '$tableName' already exists.");
        }

        # 执行SQL语句
        try {
            Db::statement($sql);
        } catch (Exception $e) {
            throw new Exception("Error executing SQL statement: " . $e->getMessage());
        }
    }
}
