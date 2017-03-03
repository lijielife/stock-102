<?php

$config = [];

$config['mail'] = [
    'reply_name' => 'r',
    'reply_email' => 'test@qq.com',
    'from_name' => 'f',
    'from_email' => 'from@qq.com',
    'smtp_host' => 'smtp.qq.com',
    'smtp_port' => 25,
    'smtp_user' => 'test@qq.com',
    'smtp_pass' => 'pwd',
];

$config['db'] = [
    'host' => 'localhost',
    'port' => '6379',
    'auth' => 'test',
];

return $config;