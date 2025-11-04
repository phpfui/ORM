<?php

\error_reporting(E_ALL);

// setup autoloader including vendors
function autoload(string $className) : void
	{
	$path = \str_replace('\\', DIRECTORY_SEPARATOR, __DIR__ . "/../{$className}.php");

	@include_once $path;
	}
\spl_autoload_register('autoload');
include __DIR__ . '//..//vendor//autoload.php';


function getSqlFile(string $baseName, string $driver) : string
	{
	$schema = $baseName . '.sql';
	$file = __DIR__ . '/../northwind/' . $driver . '/' . $schema;

	if (\file_exists($file))
		{
		$schema = $file;
		}
	else
		{
		$schema = __DIR__ . '/../northwind/' . $schema;
		}

	return $schema;
	}

function loadFile(string $file) : void
	{
	$sql = '';

	foreach (\file($file) as $line)
		{
		// Ignoring comments from the SQL script
		if (\str_starts_with((string)$line, '--') || '' == $line || \str_starts_with((string)$line, '#'))
			{
			continue;
			}

		$sql .= $line;

		if (\str_ends_with(\trim((string)$line), ';'))
			{
			\PHPFUI\ORM::execute($sql);
			$sql = '';
			}
		} // end foreach
	}

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


$config = include __DIR__ . '/../testConfig.php';
$dsn = $driver = $config['dsn'];
$driver = \substr($dsn, 0, \strpos($dsn, ':'));

$schemaFile = \getSqlFile('schema', $driver);
$dataFile = \getSqlFile('data', $driver);

if ('sqlite' == $driver && ! \str_contains($dsn, ':memory:'))
	{
	$sqliteFile = \substr($dsn, \strpos($dsn, ':') + 1);
	\fclose(\fopen($sqliteFile, 'w'));
	}

$pdo = new \PHPFUI\ORM\PDOInstance($dsn, $config['name'], $config['key']);
if ('mysql' == $driver)
	{
	$pdo->execute('set autocommit=0');
	}

\PHPFUI\ORM::addConnection($pdo);
\PHPFUI\ORM::$namespaceRoot = __DIR__ . '/..';
\PHPFUI\ORM::$recordNamespace = 'Tests\\App\\Record';
\PHPFUI\ORM::$tableNamespace = 'Tests\\App\\Table';
\PHPFUI\ORM::$migrationNamespace = 'Tests\\Fixtures\\Migration';
\PHPFUI\ORM::$idSuffix = '_id';

\PHPFUI\ORM::setTranslationCallback(\PHPFUI\Translation\Translator::trans(...));
\PHPFUI\Translation\Translator::setTranslationDirectory(__DIR__ . '/../translations');
\PHPFUI\Translation\Translator::setLocale('en_US');

// load schema
\loadFile($schemaFile);
// load data
\loadFile($dataFile);

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
