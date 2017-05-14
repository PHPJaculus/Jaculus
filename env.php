<?php

/**
 * Main configurations
 */

return [
    //App
    'app.debug' => true,
	'app.cache' => false,
	'app.maintenance_mode' => false,

    //DB
	'db.type' => 'mysql',
	'db.name' => 'name',
	'db.server' => 'localhost',
	'username' => 'your_username',
	'password' => 'your_password',
	'charset' => 'utf8',
	'db.port' => 3306,
	'db.prefix' => '',
	'db.option' => [
		PDO::ATTR_CASE => PDO::CASE_NATURAL
	]
];