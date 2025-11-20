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

function loadFile(string $file, string $driver) : void
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
			$sql = \trim($sql);

			if (! (\str_starts_with($sql, 'SET ') && 'mysql' != $driver))
				{
				\PHPFUI\ORM::execute($sql);
				$error = \PHPFUI\ORM::getLastError();

				if ($error)
					{
					echo "\nError from {$sql}:\n{$error}\n";
					}
				}
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

if ($pdo->postGre)
	{
	$pdo->execute("SET TIME ZONE 'UTC';");
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
\loadFile($schemaFile, $driver);
// load data
$transaction = new \PHPFUI\ORM\Transaction();
\loadFile($dataFile, $driver);
$transaction->commit();

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
	if ($pdo->postGre)
		{
		$tableName = '"' . $table . '"';
		$id = '"' . $table . '_id"';
		\PHPFUI\ORM::execute("SELECT setval('{$table}_{$table}_id_seq', (SELECT MAX({$id}) FROM {$tableName}));");
		}

	$modelGenerator->generate($table);
	$validatorGenerator->generate($table);
	}
$pdo->clearErrors();

//$rows = \PHPFUI\ORM::getRows("SELECT COUNT(*) from (SELECT * FROM `order_detail` GROUP BY `order_detail_id` HAVING (quantity * unit_price) >= 1000.0) countalias");
//print_r($rows);
//print_r("\n");
//print_r(\PHPFUI\ORM::getLastSQL());
