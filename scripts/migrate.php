<?php

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
