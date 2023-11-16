<?php
declare(strict_types=1);

namespace Sunsgne\WebmanSmsSend\Command;

use Exception;
use support\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @Time 2023/11/8 17:07
 * @author sunsgne
 */
class SyncCountryCodeCommand extends Command
{


    protected static $defaultName        = 'sync-country-data';
    protected static $defaultDescription = '同步導入國家/地區區域數據到country_mobile表';


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
    }

    /**
     * Confirm a question with the user.
     */
    public function confirm(string $question, bool $default = false): bool
    {

        return $this->output->confirm($question, $default);
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = $this->output->choice(
            '你确定要先截断"country_mobile"表,再导入数据吗',
            ['截断并导入', '不导入（放弃执行）'],
            '不导入（放弃执行）'
        );
        if ($status == '截断并导入') {
            try {
                $this->executeSQL();
            } catch (Exception $exception) {
                $this->output->writeln($exception->getMessage());
                return self::FAILURE;
            }
        }
        $this->output->writeln('执行完毕！' . $status);
        return self::SUCCESS;
    }


    /**
     * 执行SQL语句
     * @throws Exception
     */
    private function executeSQL(): void
    {
        if (!Db::schema()->hasTable('country_mobile')) {
            throw new Exception("Table 'country_mobile' does not exist.");
        }
        // 截断表
        try {
            $config = $this->getConfig();
            Db::beginTransaction();
            Db::table('country_mobile')->delete();
            Db::table('country_mobile')->insert($config);
            Db::commit();
        } catch (Exception $exception) {
            Db::rollBack();
            throw new Exception($exception->getMessage());
        }


    }

    /**
     * @return array
     * @throws Exception
     * @Time 2023/11/13 14:31
     * @author sunsgne
     */
    private function getConfig(): array
    {
        $this->getJson2Config();
        $config = config('plugin.sunsgne.webman-sms-send.country', []);
        if (empty($config)) {
            throw new Exception("config is empty.");
        }
        $time = time();
        foreach ($config as $k => $v) {
            $config[$k]['create_time'] = $time;
            $config[$k]['update_time'] = $time;
            $config[$k]['country_name'] = json_encode($v['country_name']);
            $config[$k]['national_flag'] = $v['country_code'].'.png';
        }
        return $config;
    }


    /**
     * 可忽略的方法
     * @return void
     * @Time 2023/11/14 14:31
     * @author sunsgne
     */
    protected function getJson2Config(): void
    {
        $jsonString = file_get_contents(BASE_PATH . '/vendor/sunsgne/webman-sms-send/src/Lib/XYCountryCode.json');
        $dataArray  = json_decode($jsonString, true);
        $config     = [];
        foreach ($dataArray as $v) {
            $config[] = [
                'country_name_zh'     => $v['zh'],
                'country_name'        => [
                    'zh' => $v['zh'],
                    'en' => $v['en'],
                    'tw' => $v['tw'],
                ],
                'country_code'        => $v['locale'],
                'country_mobile_code' => $v['code'],
            ];
        }
        $phpCode = '<?php' . PHP_EOL . 'return ' . var_export($config, true) . ';' . PHP_EOL . '?>';

        file_put_contents(BASE_PATH .'/config/plugin/sunsgne/webman-sms-send/country.php', $phpCode);
    }
}