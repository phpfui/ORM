<?php

include __DIR__ . '/../vendor/autoload.php';

//$pdo = new \PHPFUI\ORM\PDOInstance('mysql:host=localhost;dbname=northwind;port=3306;charset=utf8mb4;collation=utf8mb4_general_ci', 'root');

$pdo = new \PHPFUI\ORM\PDOInstance('sqlite:' . __DIR__ . '/../northwind/northwind.db');

\PHPFUI\ORM::addConnection($pdo);

echo "Generate Validation Models\n\n";

\array_shift($argv);

$generator = new \PHPFUI\ORM\Tool\Generate\Validator();

if (count($argv))
	{
	foreach ($argv as $table)
		{
		if ($generator->generate($table))
			{
			echo "{$table}\n";
			}
		}

	exit;
	}

$tables = \PHPFUI\ORM::getTables();
if (! \count($tables))
	{
	echo "No tables found. Check your database configuration settings.\n";

	exit;
	}

foreach ($tables as $table)
	{
	if ($generator->generate($table))
		{
		echo "{$table}\n";
		}
	}
