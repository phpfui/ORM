<?php

namespace PHPFUI;

/**
 * Static acces to the ORM
 */
class ORM
	{
	public static string $namespaceRoot = __DIR__ . '/..';

	public static string $recordNamespace = 'App\\Record';

	public static string $tableNamespace = 'App\\Table';

	public static string $migrationNamespace = 'App\\Migration';

	public static string $idSuffix = 'Id';

	private static $translationCallback = null;

	private static array $instances = [];

	private static int | string | null $currentInstance = null;

	private static ?\Psr\Log\AbstractLogger $logger = null;

	public static function getRecordNamespacePath() : string
		{
		return self::filePath(self::$namespaceRoot . '/' . self::$recordNamespace);
		}

	public static function getTableNamespacePath() : string
		{
		return self::filePath(self::$namespaceRoot . '/' . self::$tableNamespace);
		}

	public static function getMigrationNamespacePath() : string
		{
		return self::filePath(self::$namespaceRoot . '/' . self::$migrationNamespace);
		}

	/**
	 * Add a PDO instance and return the index for future reference.  Use the return value to switch bethin
	 */
	public static function addConnection(\PHPFUI\ORM\PDOInstance $pdo, string $name = '') : int | string
		{
		if ($name)
			{
			self::$currentInstance = $name;
			}
		else
			{
			self::$currentInstance = \count(self::$instances);
			}

		self::$instances[self::$currentInstance] = $pdo;

		return self::$currentInstance;
		}

	public static function setLogger(\Psr\Log\AbstractLogger $logger) : void
		{
		self::$logger = $logger;
		}

	/**
	 * Use a specific connection
	 *
	 * @return null if requested connection is not found, else returns the previously selected connection
	 */
	public static function useConnection(int | string $connection) : int | string | null
		{
		$prior = null;

		if (\array_key_exists($connection, self::$instances))
			{
			$prior = self::$currentInstance;
			self::$currentInstance = $connection;
			}

		return $prior;
		}

	/**
	 * Gets the current connection id in use
	 */
	public static function getConnection() : int | string | null
		{
		return self::$currentInstance;
		}

	/**
	 * Clears an existing errors and begins a transaction
	 */
	public static function beginTransaction() : bool
		{
		return self::getInstance()->beginTransaction();
		}

	/**
	 * Commits the current transaction
	 */
	public static function commit() : bool
		{
		return self::getInstance()->commit();
		}

	/**
	 * Executes the SQL string using the matching $input array
	 *
	 * @return bool  status of command run
	 */
	public static function execute(string $sql, array $input = []) : bool
		{
		return self::getInstance()->execute($sql, $input);
		}

	public static function describeTable(string $table) : array
		{
		return self::getInstance()->describeTable($table);
		}

	/**
	 * @return \PHPFUI\ORM\ArrayCursor  tracking the sql and input passed
	 */
	public static function getArrayCursor(string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\ArrayCursor
		{
		return self::getInstance()->getArrayCursor($sql, $input);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor  tracking the sql and input passed
	 */
	public static function getRecordCursor(\PHPFUI\ORM\Record $crud, string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\RecordCursor
		{
		return self::getInstance()->getRecordCursor($crud, $sql, $input);
		}

	/**
	 * @return \PHPFUI\ORM\DataObjectCursor  tracking the sql and input passed
	 */
	public static function getDataObjectCursor(string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\DataObjectCursor
		{
		return self::getInstance()->getDataObjectCursor($sql, $input);
		}

	public static function getTables() : array
		{
		return self::getInstance()->getTables();
		}

	public static function getIndexes(string $table) : array
		{
		return self::getInstance()->getIndexes($table);
		}

	/**
	 * @return string  error string from the most recent operation
	 */
	public static function getLastError() : string
		{
		return self::getInstance()->getLastError();
		}

	/**
	 * @return int  error code from the most recent operation
	 */
	public static function getLastErrorCode() : int
		{
		return self::getInstance()->getLastErrorCode();
		}

	/**
	 * @return array  of all errors since the last transaction or last time cleared
	 */
	public static function getLastErrors() : array
		{
		return self::getInstance()->getLastErrors();
		}

	/**
	 * @return array  of parameters from the last operation
	 */
	public static function getLastParameters() : array
		{
		return self::getInstance()->getLastParameters();
		}

	/**
	 * @return string  SQL statement with the ? inserted
	 */
	public static function getLastSql() : string
		{
		return self::getInstance()->getLastSql();
		}

	/**
	 * @return array<string, string> a single row of the first matching record or an empty array if an error
	 */
	public static function getRow(string $sql, array $input = []) : array
		{
		return self::getInstance()->getRow($sql, $input);
		}

	/**
	 * Similar to getArrayCursor except returns a fully populated array
	 *
	 * It is recommended to use getArrayCursor if you don't need array functionality
	 */
	public static function getRows(string $sql, array $input = [], int $fetchType = \PDO::FETCH_ASSOC) : array
		{
		return self::getInstance()->getRows($sql, $input, $fetchType);
		}

	/**
	 * @return string value returned from the first field in the first row returned by the querry, or blank if error
	 */
	public static function getValue(string $sql, array $input = []) : string
		{
		return self::getInstance()->getValue($sql, $input);
		}

	/**
	 * @return array<mixed> of the first value in each row from the query
	 */
	public static function getValueArray(string $sql, array $input = []) : array
		{
		return self::getInstance()->getValueArray($sql, $input);
		}

	/**
	 * @return string  primary key of the last record inserted
	 */
	public static function lastInsertId(string $name = '') : string
		{
		return self::getInstance()->lastInsertId($name);
		}

	/**
	 * Logs array of errors via error_log
	 */
	public static function log(string $type, string $message, array $context = []) : void
		{
		if (self::$logger)
			{
			self::$logger->log($type, $message, $context);
			}
		}

	/**
	 * @return ?\PHPFUI\ORM\PDOInstance the underlying PDO object
	 */
	public static function pdo() : ?\PHPFUI\ORM\PDOInstance
		{
		return self::getInstance();
		}

	/**
	 * Logs errors and clears error log
	 */
	public static function reportErrors() : void
		{
		self::getInstance()->reportErrors();
		}

	/**
	 * Rolls back the current transaction
	 */
	public static function rollBack() : bool
		{
		return self::getInstance()->rollBack();
		}

	public static function setTranslationCallback($callback) : void
		{
		self::$translationCallback = $callback;
		}

	/**
	 * Translate a field.  See [PHPFUI\Translation](http://www.phpfui.com/?n=PHPFUI%5CTranslation)
	 *
	 * @param array<mixed, mixed> $variables
	 */
	public static function trans(string $text, array $variables = []) : string
		{
		if (self::$translationCallback)
			{
			$callback = self::$translationCallback;

			return $callback($text, $variables);
			}

		return $text;
		}

	/**
	 * Executes the query and catches any errors
	 */
	public static function executeStatement(\PDOStatement $statement, array $input = []) : ?\PDOStatement
		{
		return self::getInstance()->executeStatement($statement, $input);
		}

	/**
	 * Get the correct class name from the table name
	 */
	public static function getBaseClassName(string $table) : string
		{
		$parts = \explode('_', $table);

		foreach ($parts as $index => $part)
			{
			$parts[$index] = \ucfirst($part);
			}

		return \implode('', $parts);
		}

	public static function getInstance() : \PHPFUI\ORM\PDOInstance
		{
		if (null === self::$currentInstance)
			{
			throw new \Exception('You need to call \PHPFUI\ORM::addConnection before accessing database');
			}

		return self::$instances[self::$currentInstance];
		}

	private static function filePath(string $namespace) : string
		{
		return \str_replace('\\', '/', $namespace);
		}
	}
