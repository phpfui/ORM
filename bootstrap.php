<?php

// hack for now
function trans(string $text) : string
	{
	return $text;
	}

$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../www';

\error_reporting(E_ALL);

include __DIR__ . '/../commonbase.php';

$_SERVER['SERVER_NAME'] = 'wcc';

$dbSettings = new \PHPFUI\ORM\Settings\DB();
new \PHPFUI\ORM\PDO($dbSettings);
