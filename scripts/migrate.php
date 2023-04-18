<?php

include __DIR__ . '/../vendor/autoload.php';

// example file, you will need to initialize the database
//
//$pdo = new \PHPFUI\ORM\PDOInstance('sqlite:' . __DIR__ . '/../northwind/northwind.db');
//
//\PHPFUI\ORM::addConnection($pdo);

$migrate = new \PHPFUI\ORM\Migrator();

if ((int)($argv[1] ?? ''))
	{
	$migrate->migrateTo((int)$argv[1]);
	}
else
	{
	$migrate->migrate();
	}

\print_r($migrate->getErrors());
