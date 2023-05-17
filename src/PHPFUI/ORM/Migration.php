<?php

namespace PHPFUI\ORM;

abstract class Migration
	{
	/** @var string[] */
	protected array $errors = [];

	/** @var string[] */
	protected array $myslqDefaults = [
		'CURRENT_TIMESTAMP',
		'CURRENT_DATE',
		'true',
		'false',
		"b'0'",
		"b'1'",
	];

	/** @var array<string, array> */
	private array $alters = [];

	private string $ran = '';

	/**
	 * Returns a description of the migration
	 */
	public function description() : string
		{
		return 'description not set in ' . static::class;
		}

	/**
	 * Returns true if the up migrate worked
	 */
	abstract public function down() : bool;

	/**
	 * Run all the cached alter statements.  You will need to call if this directly if you need to change a table altered in the current migration.
	 */
	public function executeAlters() : bool
		{
		// do DROP, ADD and CHANGE for each table
		foreach ($this->alters as $table => $alters)
			{
			$sql = 'ALTER TABLE `' . $table . '` ' . \implode(',', $alters);

			$this->runSQL($sql);
			}
		$this->alters = [];

		return 0 == \count($this->errors);
		}

	/**
	 * @return string[] of table names
	 */
	public function getAllTables(string $type = 'BASE TABLE') : array
		{
		return \PHPFUI\ORM::getTables();
		}

	/** @return string[] */
	public function getErrors() : array
		{
		return $this->errors;
		}

	/**
	 * @return string a MySQL setting
	 */
	public function getMySQLSetting(string $variable) : string
		{
		$result = \PHPFUI\ORM::getRows('SHOW VARIABLES where Variable_name = "' . $variable . '"');

		return $result[0]['Value'];
		}

	/**
	 * Returns the migration id. Class name should be Migration_x
	 */
	public function id() : int
		{
		$parts = \explode('_', static::class);

		return (int)$parts[1];
		}

	public function ran() : string
		{
		return $this->ran;
		}

	/**
	 * Runs the current SQL statement immediately
	 */
	public function runSQL(string $sql, array $input = []) : bool
		{
		try
			{
			\PHPFUI\ORM::pdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 1);

			if (! \PHPFUI\ORM::execute($sql, $input))
				{
				$this->errors = \array_merge($this->errors, \PHPFUI\ORM::getLastErrors());

				return false;
				}
			}
		catch (\Throwable $e)
			{
			$this->errors[] = $e->getMessage();
			$this->errors[] = $sql;

			return false;
			}

		if (false !== \stripos($sql, 'rename'))
			{
			// wait for table rename to complete, could be queries running against table, hack but good enough
			\sleep(1);
			}

		return true;
		}

	public function setRan(string $ran) : static
		{
		$this->ran = $ran;

		return $this;
		}

	/**
	 * Returns true if the up migrate worked
	 */
	abstract public function up() : bool;

	/**
	 * Always adds a column
	 */
	protected function addColumn(string $table, string $field, string $parameters) : bool
		{
		$fieldInfo = $this->getFieldInfo($table, $field);

		if ($fieldInfo)
			{
			$this->alter('DROP', $table, $field);
			}
		$this->alter('ADD', $table, $field, $parameters);

		return true;
		}

	/**
	 * Creates a foreign key on the table referencing the given table and columns.
	 */
	protected function addForeignKey(string $toTable, string $referenceTable, array $columns, string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE') : bool
		{
		$actions = ['RESTRICT', 'CASCADE', 'SET NULL', 'NO ACTION'];
		$onDelete = \strtoupper($onDelete);

		if ($onDelete && ! \in_array($onDelete, $actions))
			{
			throw new \PHPFUI\ORM\Exception('$onDelete option for ' . __METHOD__ . ' must be one of (' . \implode(',', $actions) . ") {$onDelete} given.");
			}

		$onUpdate = \strtoupper($onUpdate);

		if ($onUpdate && ! \in_array($onUpdate, $actions))
			{
			throw new \PHPFUI\ORM\Exception('$onUpdate option for ' . __METHOD__ . ' must be one of (' . \implode(',', $actions) . ") {$onUpdate} given.");
			}

		$this->addIndex($referenceTable, $columns);

		// set missing relations to null
		foreach ($columns as $column)
			{
			$foreignTable = \str_replace(\PHPFUI\ORM::$idSuffix, '', (string)$column);
			$sql = "update `{$referenceTable}` set `{$column}`=null where `{$column}` not in (select `{$column}` from `{$toTable}`)";
			$this->runSQL($sql);
			}

		$columnList = \implode(',', $columns);
		$fkName = \implode('_', $columns) . '_FK';
		$sql = "ADD CONSTRAINT {$fkName} FOREIGN KEY ({$columnList}) REFERENCES {$referenceTable}({$columnList})";

		if ($onDelete)
			{
			$sql .= ' ON DELETE ' . $onDelete;
			}

		if ($onUpdate)
			{
			$sql .= ' ON UPDATE ' . $onUpdate;
			}

		$this->alters[$toTable][] = $sql;

		return true;
		}

	/**
	 * Add an index on the fields in the array.
	 */
	protected function addIndex(string $table, string | array $fields, string $indexType = '') : bool
		{
		if (\is_string($fields))
			{
			$indexName = $fields;
			$fields = [$fields];
			}
		else
			{
			$indexName = \implode('', $fields) . $table . 'Index';
			}

		if ($this->indexExists($table, $indexName))
			{
			return false;
			}

		$sql = "CREATE {$indexType} INDEX `{$indexName}` ON `{$table}` (`" . \implode('`,`', $fields) . '`)';

		return $this->runSQL($sql);
		}

	/**
	 * Adds a primary key to the table.
	 */
	protected function addPrimaryKey(string $table, array $fields) : bool
		{
		$this->dropPrimaryKey($table);
		$keys = implode('`, `', $fields);
		$this->alters[$table] = ["ADD PRIMARY KEY (`{$keys}`)"];

		return true;
		}

	/**
	 * Adds a primary key to the table.  If $field is not specified, it will the primary key will be the table name with Id appended.  If $newFieldName is not specified, it will default to $field. This method works on an existing field only.
	 */
	protected function addPrimaryKeyAutoIncrement(string $table, string $field = '', string $newFieldName = '') : bool
		{
		if (empty($field))
			{
			$field = $table . \PHPFUI\ORM::$idSuffix;
			}

		if (empty($newFieldName))
			{
			$newFieldName = $field;
			}
		$this->dropPrimaryKey($table);
		$this->alters[$table] = ["change `{$field}` `{$newFieldName}` int NOT NULL primary key auto_increment"];

		return true;
		}

	/**
	 * Alters a column incluing a reneme if $newName is provided
	 */
	protected function alterColumn(string $table, string $field, string $parameters, string $newName = '') : bool
		{
		$fieldInfo = $this->getFieldInfo($table, $field);

		if ($fieldInfo)
			{
			$this->alter('CHANGE', $table, $field, $parameters, $newName);
			}
		else
			{
			$this->alter('ADD', $table, $field, $parameters);
			}

		return true;
		}

	/**
	 * Duplicate rows with the same key values will be deleted
	 *
	 * @param array<string> $keys
	 */
	protected function deleteDuplicateRows(string $table, array $keys) : bool
		{
		$fields = '`' . \implode('`,`', $keys) . '`';
		$sql = "select count(*) number,{$fields} from `{$table}` group by {$fields} having number > 1";
		$rows = \PHPFUI\ORM::getArrayCursor($sql);

		foreach ($rows as $row)
			{
			$count = (int)$row['number'] - 1;
			$where = $comma = '';
			$input = [];

			foreach ($keys as $key)
				{
				// @phpstan-ignore-next-line
				if (is_null($row[$key]))
					{
					$where .= "{$comma}`{$key}` is null";
					}
				else
					{
					$input[] = $row[$key];
					$where .= "{$comma}`{$key}`=?";
					}
				$comma = ' and ';
				}
			$sql = "delete from `{$table}` where {$where} limit {$count}";
			\PHPFUI\ORM::execute($sql, $input);
			}

		return true;
		}

	/**
	 * Drops all indexes on a table but not the primary key.
	 */
	protected function dropAllIndexes(string $table) : void
		{
		$dropped = [];
		$rows = \PHPFUI\ORM::getRows("SHOW INDEX FROM `{$table}`");

		foreach ($rows as $row)
			{
			$indexName = $row['Key_name'];

			if (! isset($dropped[$indexName]))
				{
				$dropped[$indexName] = 1;
				$this->runSQL("DROP INDEX `{$indexName}` ON `{$table}`");
				}
			}
		}

	/**
	 * Drops a column if it exists
	 */
	protected function dropColumn(string $table, string $field) : bool
		{
		$fieldInfo = $this->getFieldInfo($table, $field);

		if ($fieldInfo)
			{
			$this->alter('DROP', $table, $field);
			}

		return true;
		}

	/**
	 * Drops the foreign key on the table
	 */
	protected function dropForeignKey(string $table, array $columns) : bool
		{
		$index = \implode('_', $columns) . '_FK';

		if ($this->indexExists($table, $index))
			{
			$sql = 'DROP FOREIGN KEY ' . \implode('_', $columns) . '_FK';
			$this->alters[$table][] = $sql;
			}

		return true;
		}

	/**
	 * Drops an index by the name used by addIndex
	 */
	protected function dropIndex(string $table, string | array $fields) : bool
		{
		if (\is_string($fields))
			{
			$indexName = $fields;
			}
		else
			{
			$indexName = \implode('', $fields) . $table . 'Index';
			}

		if (! $this->indexExists($table, $indexName))
			{
			return true;
			}

		$sql = "DROP INDEX `{$indexName}` ON `{$table}`";

		return $this->runSQL($sql);
		}

	/**
	 * Drops the primary key
	 */
	protected function dropPrimaryKey(string $table) : bool
		{
		$rows = \PHPFUI\ORM::getArrayCursor("SHOW COLUMNS FROM {$table}");

		foreach ($rows as $row)
			{
			if ('PRI' == $row['Key'])
				{
				$sql = 'alter table ' . $table;

				if ('auto_increment' == $row['Extra'])
					{
					$nullable = 'NO' == $row['Null'] ? 'NOT NULL' : '';
					$sql .= " change {$row['Field']} {$row['Field']} {$row['Type']} {$nullable},";
					}
				$sql .= ' DROP PRIMARY KEY';
				$this->runSQL($sql);

				return true;
				}
			}

		return false;
		}

	/**
	 *  Drops a table if it exists
	 */
	protected function dropTable(string $table) : bool
		{
		return $this->runSQL('DROP TABLE IF EXISTS `' . $table . '`');
		}

	/**
	 * Drops tables contained in the array
	 */
	protected function dropTables(array $tables) : void
		{
		foreach ($tables as $table)
			{
			$this->dropTable($table);
			}
		}

	/**
	 * Drops a view if it exists
	 */
	protected function dropView(string $view) : bool
		{
		return $this->runSQL('DROP VIEW IF EXISTS ' . $view);
		}

	/**
	 * Drops views contained in the array
	 */
	protected function dropViews(array $views) : void
		{
		foreach ($views as $view)
			{
			$this->dropView($view);
			}
		}

	/**
	 * Tests for existance of an index on the table
	 */
	protected function indexExists(string $table, string $indexName) : bool
		{
		$rows = \PHPFUI\ORM::getRows("SHOW INDEX FROM `{$table}`");

		foreach ($rows as $row)
			{
			if ($row['Key_name'] == $indexName)
				{
				return true;
				}
			}

		return false;
		}

	/**
	 * Renames an existing table
	 */
	protected function renameTable(string $oldName, string $newName) : bool
		{
		$this->dropTable($newName);

		return $this->runSQL("rename table `{$oldName}` to `{$newName}`");
		}

	private function alter(string $type, string $table, string $field, string $extra = '', string $newName = '') : void
		{
		$sql = $type . ' COLUMN `' . $field . '`';

		if ('CHANGE' == $type)
			{
			$field = $newName ?: $field;
			$sql .= ' `' . $field . '`';
			}

		if ('DROP' != $type)
			{
			$sql .= ' ' . $extra;
			}

		if (! isset($this->alters[$table]))
			{
			$this->alters[$table] = [];
			}
		$this->alters[$table][] = $sql;
		}

	private function getFieldInfo(string $table, string $fieldName) : ?\PHPFUI\ORM\Schema\Field
		{
		$fields = \PHPFUI\ORM::describeTable($table);

		foreach ($fields as $field)
			{
			if ($field->name == $fieldName)
				{
				return $field;
				}
			}

		return null;
		}
	}
