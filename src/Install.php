<?php

namespace Sunsgne\WebmanSmsSend;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static array $pathRelation
        = array(
            'config/plugin/sunsgne/webman-sms-send' => 'config/plugin/sunsgne/webman-sms-send',

        );
    /**
     * @var array|string[]
     */
    protected static array $fileRelation = [
        'Support/RedisScope.php'                    => 'support/RedisScope.php',
    ];

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            copy_dir(__DIR__ . "/$source", base_path() . "/$dest");
            echo "Create $dest
";
        }
    }


    public static function installByFileRelation(): void
    {
        foreach (static::$fileRelation  as $source => $dest) { 
           if (is_file(base_path() . "/$dest")){
               echo  base_path() . "/$dest" .'文件已存在'.PHP_EOL;
               return;
           }
            if (!is_file(__DIR__ . "/$source")){
               echo __DIR__ . "/$source" . '文件不存在'.PHP_EOL;
               return;
           }

            copy(__DIR__ . "/$source" , base_path() . "/$dest");
            echo "Create $dest
";
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path() . "/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            echo "Remove $dest
";
            if (is_file($path) || is_link($path)) {
                unlink($path);
                continue;
            }
            remove_dir($path);
        }
    }

}
