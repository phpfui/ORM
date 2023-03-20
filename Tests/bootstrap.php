<?php

\error_reporting(E_ALL);

	// allow the autoloader to be included from any script that needs it.
function autoload(string $className) : void
	{
	$path = \str_replace('\\', DIRECTORY_SEPARATOR, __DIR__ . "/../{$className}.php");

	@include_once $path;
	}

\spl_autoload_register('autoload');

include __DIR__ . '//..//vendor//autoload.php';

// generate models and validators

$files = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator(__DIR__ . '/App', RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::CHILD_FIRST
);

foreach ($files as $fileinfo)
	{
	if ($fileinfo->isFile() && \str_ends_with($fileinfo->getRealPath(), '.php'))
		{
	\unlink($fileinfo->getRealPath());
		}
	}

$pdo = new \PHPFUI\ORM\PDOInstance('sqlite:' . __DIR__ . '/../northwind/northwind.db');

\PHPFUI\ORM::addConnection($pdo);
\PHPFUI\ORM::$namespaceRoot = __DIR__ . '/..';
\PHPFUI\ORM::$recordNamespace = 'Tests\\App\\Record';
\PHPFUI\ORM::$tableNamespace = 'Tests\\App\\Table';
\PHPFUI\ORM::$migrationNamespace = 'Tests\\App\\Migration';
\PHPFUI\ORM::$idSuffix = '_id';

\PHPFUI\ORM::setTranslationCallback(\PHPFUI\Translation\Translator::trans(...));
\PHPFUI\Translation\Translator::setTranslationDirectory(__DIR__ . '/../translations');
\PHPFUI\Translation\Translator::setLocale(\Locale::getDefault());

$tables = \PHPFUI\ORM::getTables();

if (! \count($tables))
	{
	echo "No tables found. Check your configuration.\n";

	exit;
	}

$modelGenerator = new \PHPFUI\ORM\Tool\Generate\CRUD();
$validatorGenerator = new \PHPFUI\ORM\Tool\Generate\Validator();

foreach ($tables as $table)
	{
	$modelGenerator->generate($table);
	$validatorGenerator->generate($table);
	}
