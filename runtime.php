<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/11
 * Time: 16:15
 */

require(__DIR__ . '/vendor/autoload.php');

// 加载配置
try {
    $dotEnv = new \Dotenv\Dotenv(__DIR__);
    $dotEnv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    exit($e->getMessage());
}

$env = env('APP_ENV', 'pre');

defined('YII_ENV') or define('YII_ENV', $env);

switch (YII_ENV) {
    case 'prod': // 生产
    case 'pre': //预发布
        defined('YII_DEBUG') or define('YII_DEBUG', false);
        break;
    default:// 其它
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        break;
}
return $env;