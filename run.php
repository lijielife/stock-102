<?php
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('PRC');

require_once('vendor/autoload.php');
include __DIR__ . '/stock.class.php';

$config = include __DIR__ . '/config.php';
extract($config);

$code = array_merge(array_keys($notice), array_keys($own));
$code = array_unique($code);

//
$stock = new stock();
if ($stock->isClose())
{
    die();
}
$list = $stock->query($code);

if (empty($list)) die();
$own = $stock->reorgData($own, $list);
echo "\n" . date('Y-m-d H:i:s') . "\n";

if (php_sapi_name() == 'cli')
{
    $climate = new League\CLImate\CLImate;
    $climate->table($own);
}
else
{
    foreach ($own as $id => $v )
    {
        $old = $v[5]=='-' ? $v[2] : $v[5];
        echo $id . ":\t" . $old . "\t" . $v[1] . "\t" . $v[4] . "\t" . $v[7] . "\t" . $v[8] . "\t" . $v[9] . "\t\n";
    }
}

// 预警处理
$redis = new Redis();
$redis->connect($db['host'], $db['port']);
$redis->auth($db['auth']);
$notice = $stock->notice($notice, $list, $redis);

