<?php

namespace PHPFUI\ORM\Interface;

interface PDOInstance
	{
	/**
	 * @param ?array<int, string|int|bool> $options
	 */
	public function __construct(
		string $dsn,
		?string $username = null,
		#[\SensitiveParameter]
		?string $password = null,
		?array $options = null
	);

	public function getPostGre() : bool;

	public function getSqlite() : bool;

	public function beginTransaction() : bool;

	/**
	 * Clears an existing errors
	 */
	public function clearErrors() : void;

	public function commit() : bool;

	/**
	 * @param ?array<int, string|int|bool> $options
	 */
	public static function connect(
		string $dsn,
		?string $username = null,
		#[\SensitiveParameter]
		?string $password = null,
		?array $options = null
	) : static;

	/**
	 * @return array<string, \PHPFUI\ORM\Schema\Field>
	 */
	public function describeTable(string $table) : array;

	public function errorCode() : ?string;

	/**
	 * @return array<string>
	 */
	public function errorInfo() : array;

	public function exec(string $statement) : int|false;

	/**
	 * Executes the SQL string using the matching $input array
	 *
	 * @param array<mixed> $input
	 *
	 * @return bool  status of command run
	 */
	public function execute(string $sql, array $input = []) : bool;

	/**
	 * Executes the query and catches any errors
	 *
	 * @param array<mixed> $input
	 */
	public function executeStatement(\PDOStatement $statement, array $input = []) : ?\PDOStatement;

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\ArrayCursor  tracking the sql and input passed
	 */
	public function getArrayCursor(string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\ArrayCursor;

	public function getAttribute(int $attribute) : mixed;

	/**
	 * @return array<string>
	 */
	public static function getAvailableDrivers() : array;

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\DataObjectCursor  tracking the sql and input passed
	 */
	public function getDataObjectCursor(string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\DataObjectCursor;

	public function getDSN() : string;

	/**
	 * @return array<string, \PHPFUI\ORM\Schema\ForeignKey>
	 */
	public function getForeignKeys(string $table) : array;

	/**
	 * @return array<\PHPFUI\ORM\Schema\Index>
	 */
	public function getIndexes(string $table) : array;

	/**
	 * @return string  error string from the most recent operation
	 */
	public function getLastError() : string;

	/**
	 * @return int  error code from the most recent operation
	 */
	public function getLastErrorCode() : int;

	/**
	 * @return array<array<string,string>> all errors since the last transaction or last time cleared
	 */
	public function getLastErrors() : array;

	/**
	 * @return array<mixed> parameters from the last operation
	 */
	public function getLastParameters() : array;

	/**
	 * @return string  SQL statement with the ? inserted
	 */
	public function getLastSql() : string;

	/**
	 * @param array<mixed> $input
	 */
	public function getPreparedStatement(string $sql, array $input = []) : \PDOStatement;

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\RecordCursor tracking the sql and input passed
	 */
	public function getRecordCursor(\PHPFUI\ORM\Record $crud, string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\RecordCursor;

	/**
	 * @param array<mixed> $input
	 *
	 * @return array<string, string> a single row of the first matching record or an empty array if an error
	 */
	public function getRow(string $sql, array $input = []) : array;

	/**
	 * Similar to getArrayCursor except returns a fully populated array
	 *
	 * It is recommended to use getArrayCursor if you don't need array functionality
	 *
	 * @param array<mixed> $input
	 *
	 * @return array<array<string,string>>
	 */
	public function getRows(string $sql, array $input = [], int $fetchType = \PDO::FETCH_ASSOC) : array;

	/**
	 * @return array<string>
	 */
	public function getTables() : array;

	/**
	 * @param array<mixed> $input
	 *
	 * @return string value returned from the first field in the first row returned by the querry, or blank if error
	 */
	public function getValue(string $sql, array $input = []) : string;

	/**
	 * @param array<mixed> $input
	 *
	 * @return array<mixed> of the first value in each row from the query
	 */
	public function getValueArray(string $sql, array $input = []) : array;

	public function inTransaction() : bool;

	public function lastInsertId(?string $name = null) : string|false;

	/**
	 * Logs array of errors via logger
	 *
	 * @param array<mixed> $context
	 */
	public function log(string $level, string $message, array $context = []) : void;

	/**
	 * @param array<int, string|int|bool> $options
	 */
	public function prepare(string $query, array $options = []) : \PDOStatement|false;

	public function query(
		string $query,
		?int $fetchMode = \PDO::FETCH_CLASS
	) : \PDOStatement|false;

	public function quote(string $string, int $type = \PDO::PARAM_STR) : string|false;

	/**
	 * Logs errors and clears error log
	 */
	public function reportErrors() : void;

	public function rollBack() : bool;

	public function setAttribute(int $attribute, mixed $value) : bool;
	}
