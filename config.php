<?php

$config = [];

$config['notice'] = [
    '000001' => array(3000,3060,3100,3170,3215,3255,3300),
	'002024' => array(10, 11),
];

$config['own'] = [
    //'000875' => array(9.863, 900),
	//'000721' => array(8.668,800),
	'002024' => array(13.567, 300),
	//'601006' => array(8.795, 200),
	//'600674' => array(10.893,400),
	//'600744' => array(8.571, 100),
	//'600839' => array(6.213, 400),
];

$config['db'] = [
    'host' => 'localhost',
    'port' => '6379',
    'auth' => 'test',
];

return $config;