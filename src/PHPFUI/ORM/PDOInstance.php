<?php

namespace PHPFUI\ORM;

class PDOInstance extends \PDO
	{
	public readonly bool $postGre;

	public readonly bool $sqlite;

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
		$this->postGre = \str_starts_with($dsn, 'pgsql');
		$this->sqlite = \str_starts_with($dsn, 'sqlite');
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
	 * Clears an existing errors
	 */
	public function clearErrors() : void
		{
		$this->lastError = [];
		$this->lastErrorCode = 0;
		$this->lastErrors = [];
		$this->lastParameters = [];
		$this->lastSql = '';
		}

	/**
	 * @return array<string, \PHPFUI\ORM\Schema\Field>
	 */
	public function describeTable(string $table) : array
		{
		$fields = [];
		$autoIncrement = false;

		if ($this->sqlite)
			{
			$autoIncrement = (bool)$this->getValue("SELECT count(*) FROM sqlite_master where tbl_name='{$table}' and sql like '%autoincrement%'");
			$rows = $this->getRows("pragma table_info('{$table}')");
			}
		elseif ($this->postGre)
			{
			$sql = 'SELECT column_name as "Field",data_type as "Type",character_maximum_length,is_nullable as "Null",column_default as "Default"
				FROM information_schema.columns
				WHERE table_schema = \'public\' AND table_name = ?
				ORDER BY ordinal_position;';

			$rows = $this->getRows($sql, [$table]);

			foreach ($rows as $index => $row)
				{
				if ('character varying' == $row['Type'] && null != $row['character_maximum_length'])
					{
					$row['Type'] = 'varchar(' . $row['character_maximum_length'] . ')';
					}
				elseif ('text' == $row['Type'])
					{
					$row['Type'] = 'longtext';
					}
				$row['Extra'] = $row['Default'] ? \str_replace('nextval', 'auto_increment', $row['Default']) : '';
				$rows[$index] = $row;
				}
			}
		else
			{
			$rows = $this->getRows("describe `{$table}`;");
			}

		$fields = [];

		foreach ($rows as $row)
			{
			$field = new \PHPFUI\ORM\Schema\Field($this, $row, $autoIncrement);
			$fields[$field->name] = $field;
			}

		if ($this->postGre)
			{
			// get non auto increment primary keys
			$rows = $this->getRows("SELECT kcu.column_name as name, tc.constraint_type as sql FROM information_schema.table_constraints AS tc
														 JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
														 WHERE tc.constraint_type = 'PRIMARY KEY' AND tc.table_name = ?", [$table]);

			foreach ($rows as $row)
				{
				$fields[$row['name']]->primaryKey = true;
				}
			$row = $this->getRow("SELECT column_name as name,column_default as sql FROM information_schema.columns WHERE table_name = ? AND column_default LIKE 'nextval(%'", [$table]);

			if (\count($row))
				{
				$fields[$row['name']]->autoIncrement = true;
				$fields[$row['name']]->defaultValue = null;
				}
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
		return new \PHPFUI\ORM\ArrayCursor($this->getPreparedStatement($sql, $input), $input);
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\DataObjectCursor  tracking the sql and input passed
	 */
	public function getDataObjectCursor(string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\DataObjectCursor
		{
		return new \PHPFUI\ORM\DataObjectCursor($this->getPreparedStatement($sql, $input), $input);
		}

	public function getDSN() : string
		{
		return $this->dsn;
		}

	/**
	 * @return array<string, \PHPFUI\ORM\Schema\ForeignKey>
	 */
	public function getForeignKeys(string $table) : array
		{
		$fields = [];
		$rows = [];

		if ($this->sqlite)
			{
			$rows = \PHPFUI\ORM::getRows("PRAGMA foreign_key_list('{$table}');");
			}
		elseif ($this->postGre)
			{
			$rows = \PHPFUI\ORM::getRows('SELECT
						tc.constraint_name as CONSTRAINT_NAME,
						tc.table_name AS "TABLE_NAME",
						kcu.column_name AS "from",
						ccu.table_name AS "table",
						ccu.column_name AS "to"
				FROM
						information_schema.table_constraints AS tc
				JOIN
						information_schema.key_column_usage AS kcu
						ON tc.constraint_name = kcu.constraint_name
						AND tc.table_schema = kcu.table_schema
				JOIN
						information_schema.constraint_column_usage AS ccu
						ON ccu.constraint_name = tc.constraint_name
						AND ccu.table_schema = tc.table_schema
				WHERE
						tc.table_name = ?;', [$table]);
			}
		else
			{
			$rows = $this->getRows("SELECT
				CONSTRAINT_NAME,
				DELETE_RULE as `on_delete`,
				UPDATE_RULE as `on_update`,
				UNIQUE_CONSTRAINT_NAME as 'to',
				MATCH_OPTION as 'match',
				REFERENCED_TABLE_NAME as `table`
				FROM information_schema.referential_constraints WHERE `table_name` = ?", [$table]);
			}

		foreach ($rows as $row)
			{
			$row['TABLE_NAME'] = $table;
			$index = new \PHPFUI\ORM\Schema\ForeignKey($this, $row);
			$fields[$index->name] = $index;
			}

		return $fields;
		}

	/**
	 * @return array<\PHPFUI\ORM\Schema\Index>
	 */
	public function getIndexes(string $table) : array
		{
		$fields = [];

		if ($this->sqlite)
			{
			$rows = $this->getRows("SELECT * FROM sqlite_master WHERE type = 'index' and tbl_name='{$table}'");
			}
		elseif ($this->postGre)
			{
			// get auto increment primary keys
			$fields = $this->getRow("SELECT column_name as name,column_default as sql FROM information_schema.columns WHERE table_name = ? AND column_default LIKE 'nextval(%'", [$table]);

			if (\count($fields))
				{
				$index = new \PHPFUI\ORM\Schema\Index($this, []);
				$index->primaryKey = true;
				$index->name = $fields['name'];
				$index->extra = $fields['sql'];
				$fields[$index->name] = $index;
				}

			// get non auto increment primary keys
			$rows = $this->getRows("SELECT kcu.column_name as name, tc.constraint_type as sql FROM information_schema.table_constraints AS tc
														 JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
														 WHERE tc.constraint_type = 'PRIMARY KEY' AND tc.table_name = ?", [$table]);

			foreach ($rows as $row)
				{
				$index = new \PHPFUI\ORM\Schema\Index($this, []);
				$index->primaryKey = true;
				$index->name = $fields['name'];
				$index->extra = $fields['sql'];

				if (! isset($fields[$index->name]))
					{
					$fields[$index->name] = $index;
					}
				}
			// get the rest of the index fields
			$rows = $this->getRows('SELECT * FROM pg_indexes WHERE tablename = ?', [$table]);

			foreach ($rows as $row)
				{
				$name = \substr($row['indexname'], \strlen($table) + 1);

				if (! isset($fields[$name]))
					{
					$index = new \PHPFUI\ORM\Schema\Index($this, []);
					$index->primaryKey = false;
					$index->name = $name;
					$index->extra = $row['indexdef'];
					$fields[$name] = $index;
					}
				}

			return $fields;
			}
		else
			{
			$rows = $this->getRows("SHOW INDEXES FROM `{$table}`;");
			}

		foreach ($rows as $row)
			{
			$index = new \PHPFUI\ORM\Schema\Index($this, $row);
			$fields[] = $index;
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
	 */
	public function getPreparedStatement(string $sql, array $input = []) : \PDOStatement
		{
		$this->lastParameters = $input;

		if ($this->postGre)
			{
			$sql = \str_replace('`', '"', $sql);
			}
		$this->lastSql = $sql;

		return $this->prepare($sql);
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return \PHPFUI\ORM\RecordCursor tracking the sql and input passed
	 */
	public function getRecordCursor(\PHPFUI\ORM\Record $crud, string $sql = 'select 0 limit 0', array $input = []) : \PHPFUI\ORM\RecordCursor
		{
		return new \PHPFUI\ORM\RecordCursor($crud, $this->getPreparedStatement($sql, $input), $input);
		}

	/**
	 * @param array<mixed> $input
	 *
	 * @return array<string, string> a single row of the first matching record or an empty array if an error
	 */
	public function getRow(string $sql, array $input = []) : array
		{
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

		return \PHPFUI\ORM::expandResources($returnValue);
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
		$statement = $this->run($sql, $input);

		if (null === $statement)
			{
			return [];
			}

		return $statement->fetchAll($fetchType);
		}

	/**
	 * @return array<string>
	 */
	public function getTables() : array
		{
		if ($this->sqlite)
			{
			$rows = $this->getRows('SELECT name FROM sqlite_schema WHERE type="table" AND name NOT LIKE "sqlite_%"');
			}
		elseif ($this->postGre)
			{
			$rows = $this->getRows("SELECT table_name as name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE';");
			}
		else
			{
			$rows = $this->getRows('show tables');
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
	private function run(string $sql, array $input) : ?\PDOStatement
		{
		return $this->executeStatement($this->getPreparedStatement($sql, $input), $input);
		}
	}
