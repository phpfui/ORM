<?php

namespace PHPFUI\ORM;

class PDOInstance extends \PDO
	{
	/** @var array<string> */
	private array $lastError = [];

	private int $lastErrorCode = 0;

	/** @var array<array<string,string>> */
	private array $lastErrors = [];

	/** @var array<mixed> */
	private array $lastParameters = [];

	private string $lastSql = '';

	/**
	 * @param ?array<string,string> $options
	 */
	public function __construct(private string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
		{
		parent::__construct($dsn, $username, $password, $options);
		}

	/**
	 * Clears an existing errors and begins a transaction
	 */
	public function beginTransaction() : bool
		{
		$this->reportErrors();

		return parent::beginTransaction();
		}

	/**
	 * @return array<\PHPFUI\ORM\Schema\Field>
	 */
	public function describeTable(string $table) : array
		{
		$fields = [];
		$autoIncrement = false;

		if (\str_starts_with($this->dsn, 'mysql'))
			{
			$rows = $this->getRows("describe `{$table}`;");
			}
		else
			{
			$autoIncrement = (bool)$this->getValue("SELECT count(*) FROM sqlite_master where tbl_name='{$table}' and sql like '%autoincrement%'");
			$rows = $this->getRows("pragma table_info('{$table}')");
			}

		foreach ($rows as $row)
			{
			$fields[] = new \PHPFUI\ORM\Schema\Field($this, $row, $autoIncrement);
			}

		return $fields;
		}

	/**
	 * Executes the SQL string using the matching $input array
	 *
	 * @param array<mixed> $input
	 *
	 * @return bool  status of command run
	 */
	public function execute(string $sql, array $input = []) : bool
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;

		return null !== $this->run($sql, $input);
		}

	/**
	 * Executes the query and catches any errors
	 *
	 * @param array<mixed> $input
	 */
	public function executeStatement(\PDOStatement $statement, array $input = []) : ?\PDOStatement
		{
		$this->lastErrorCode = 0;
		$this->lastError = [];

		try
			{
			$returnValue = $statement->execute($input);
			}
		catch (\PDOException)
			{
			$returnValue = false;
			}
		$this->lastErrorCode = (int)$statement->errorCode();
		// save last statement for potential use of column data

		if (! $returnValue || $this->lastErrorCode)
			{
			$this->lastError = $statement->errorInfo();
			\ob_start();
			$statement->debugDumpParams();
			$info = \ob_get_contents();
			\ob_end_clean();
			$data = ['sql' => $info,
				'input' => $input,
				'error' => $this->lastError[2],
				'stack' => \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS),
			];
			$this->lastErrors[] = $data;
			$this->log(\Psr\Log\LogLevel::ERROR, 'Error from ' . $this->lastSql, $data);
			$statement = null;
			}

		return $statement;
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\ArrayCursor  tracking the sql and input passed
	 */
	public function getArrayCursor(string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\ArrayCursor
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;

		return new \PHPFUI\ORM\ArrayCursor($this->prepare($sql), $input);
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\DataObjectCursor  tracking the sql and input passed
	 */
	public function getDataObjectCursor(string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;

		return new \PHPFUI\ORM\DataObjectCursor($this->prepare($sql), $input);
		}

	public function getDSN() : string
		{
		return $this->dsn;
		}

	/**
	 * @return array<\PHPFUI\ORM\Schema\Index>
	 */
	public function getIndexes(string $table) : array
		{
		$fields = [];

		if (\str_starts_with($this->dsn, 'mysql'))
			{
			$rows = $this->getRows('SHOW INDEXES FROM ' . $table);
			}
		else
			{
			$rows = $this->getRows("SELECT * FROM sqlite_master WHERE type = 'index' and tbl_name='{$table}'");
			}

		foreach ($rows as $row)
			{
			$fields[] = new \PHPFUI\ORM\Schema\Index($this, $row);
			}

		return $fields;
		}

	/**
	 * @return string  error string from the most recent operation
	 */
	public function getLastError() : string
		{
		if (\count($this->lastError))
			{
			return $this->lastError[2] ?? '';
			}

		return '';
		}

	/**
	 * @return int  error code from the most recent operation
	 */
	public function getLastErrorCode() : int
		{
		return $this->lastErrorCode;
		}

	/**
	 * @return array<array<string,string>> all errors since the last transaction or last time cleared
	 */
	public function getLastErrors() : array
		{
		return $this->lastErrors;
		}

	/**
	 * @return array<mixed> parameters from the last operation
	 */
	public function getLastParameters() : array
		{
		return $this->lastParameters;
		}

	/**
	 * @return string  SQL statement with the ? inserted
	 */
	public function getLastSql() : string
		{
		return $this->lastSql;
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\RecordCursor tracking the sql and input passed
	 */
	public function getRecordCursor(\PHPFUI\ORM\Record $crud, string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\RecordCursor
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;

		return new \PHPFUI\ORM\RecordCursor($crud, $this->prepare($sql), $input);
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return array<string, string> a single row of the first matching record or an empty array if an error
	 */
	public function getRow(string $sql, array $input = []) : array
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;

		$statement = $this->run($sql, $input);

		if (null === $statement)
			{
			return [];
			}
		$returnValue = $statement->fetch(\PDO::FETCH_ASSOC);

		if (! \is_array($returnValue))
			{
			$returnValue = [];
			}

		return $returnValue;
		}

	/**
	 * Similar to getArrayCursor except returns a fully populated array
	 *
	 * It is recommended to use getArrayCursor if you don't need array functionality
	 *
	 * @param array<mixed> $input
	 *
	 * @return array<array<string,string>>
	 */
	public function getRows(string $sql, array $input = [], int $fetchType = \PDO::FETCH_ASSOC) : array
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;
		$statement = $this->run($sql, $input);

		if (null === $statement)
			{
			return [];
			}
		$returnValue = $statement->fetchAll($fetchType);

		if (! \is_array($returnValue))
			{
			$returnValue = [];
			}

		return $returnValue;
		}

	/**
	 * @return array<string>
	 */
	public function getTables() : array
		{
		if (\str_starts_with($this->dsn, 'mysql'))
			{
			$rows = $this->getRows('show tables');
			}
		else
			{
			$rows = $this->getRows('SELECT name FROM sqlite_schema WHERE type="table" AND name NOT LIKE "sqlite_%"');
			}
		$tables = [];

		foreach ($rows as $row)
			{
			$tables[] = \array_pop($row);
			}

		return $tables;
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return string value returned from the first field in the first row returned by the querry, or blank if error
	 */
	public function getValue(string $sql, array $input = []) : string
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;
		$statement = $this->run($sql, $input);

		if (null === $statement)
			{
			return '';
			}
		$row = $statement->fetch(\PDO::FETCH_NUM);

		if (empty($row))
			{
			return '';
			}

		return $row[0] ?? '';
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return array<mixed> of the first value in each row from the query
	 */
	public function getValueArray(string $sql, array $input = []) : array
		{
		$this->lastParameters = $input;
		$this->lastSql = $sql;
		$statement = $this->run($sql, $input);

		if (null === $statement)
			{
			return [];
			}
		$rows = $statement->fetchAll(\PDO::FETCH_COLUMN);

		return $rows;
		}

	/**
	 * Logs array of errors via logger
	 *
	 * @param array<mixed> $context
	 */
	public function log(string $level, string $message, array $context = []) : void
		{
		\PHPFUI\ORM::log($level, $message, $context);
		}

	/**
	 * Logs errors and clears error log
	 */
	public function reportErrors() : void
		{
		if ($this->lastErrors)
			{
			$this->log(\Psr\Log\LogLevel::ERROR, 'Current Errors', $this->lastErrors);
			$this->lastErrors = [];
			}
		}

	/**
	 * Runs the query and sets and records errors
	 *
	 * @param array<mixed> $input
	 */
	private function run(string $sql, array $input = []) : ?\PDOStatement
		{
		return $this->executeStatement($this->prepare($sql), $input);
		}
	}
