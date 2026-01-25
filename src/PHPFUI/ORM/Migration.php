<?php

namespace PHPFUI\ORM;

abstract class Migration
	{
	/** @var string[] */
	protected array $errors = [];

	/** @var array<string, array<string>> */
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
			$sql = "ALTER TABLE `{$table}` " . \implode(',', $alters);
			$this->runSQL($sql);
			}
		$this->alters = [];

		return 0 == \count($this->errors);
		}

	/**
	 * @return string[] of table names
	 */
	public function getAllTables() : array
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
		$result = \PHPFUI\ORM::getRows("SHOW VARIABLES where Variable_name = `{$variable}`");

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
	 *
	 * @param array<mixed> $input
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
	 *
	 * @param array<string> $columns
	 */
	protected function addForeignKey(string $table, string $referenceTable, array $columns, string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE') : bool
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

		// set missing relations to null
		foreach ($columns as $column)
			{
			$this->addIndex($referenceTable, [$column]);
			$sql = "update `{$referenceTable}` set `{$column}`=null where `{$column}` not in (select `{$column}` from `{$table}`)";
			$this->runSQL($sql);
			}

		$columnList = \implode(',', $columns);
		$fkName = 'fk_' . $table . '_' . \implode('_', $columns);
		$sql = "ADD CONSTRAINT {$fkName} FOREIGN KEY ({$columnList}) REFERENCES {$referenceTable}({$columnList})";

		if ($onDelete)
			{
			$sql .= ' ON DELETE ' . $onDelete;
			}

		if ($onUpdate)
			{
			$sql .= ' ON UPDATE ' . $onUpdate;
			}

		$this->addAlter($table, $sql);

		return true;
		}

	/**
	 * Add an index on the fields in the array.
	 *
	 * @param array<string>|string $fields
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
	 *
	 * @param array<string> $fields
	 */
	protected function addPrimaryKey(string $table, array $fields) : bool
		{
		$this->dropPrimaryKey($table);
		$keys = \implode('`, `', $fields);
		$this->addAlter($table, "ADD PRIMARY KEY (`{$keys}`)");

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
		$this->addAlter($table, "change `{$field}` `{$newFieldName}` int NOT NULL primary key auto_increment");

		return true;
		}

	/**
	 * Alters a column type. Use renameColumn to change the column name
	 */
	protected function alterColumn(string $table, string $field, string $parameters) : bool
		{
		$fieldInfo = $this->getFieldInfo($table, $field);

		if ($fieldInfo)
			{
			$this->alter('CHANGE', $table, $field, $parameters);
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
				if (null === $row[$key])	// @phpstan-ignore-line
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
		$indexes = \PHPFUI\ORM::getIndexes($table);

		foreach ($indexes as $index)
			{
			$indexName = $index->keyName;

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
	 *
	 * @param string | array<string> $columns use string for specific name, or columns for a generated name
	 */
	protected function dropForeignKey(string $table, string | array $columns) : bool
		{
		if (\is_array($columns))
			{
			$index = "fk_{$table}_" . \implode('_', $columns);
			}
		else
			{
			$index = $columns;
			}

		$sql = 'DROP FOREIGN KEY ' . $index;
		$this->addAlter($table, $sql);

		return true;
		}

	/**
	 * Drops an index by the name used by addIndex
	 *
	 * @param array<string>|string $fields
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

		if (\PHPFUI\ORM::getInstance()->sqlite)
			{
			$sql = "DROP INDEX `{$indexName}`";
			}
		else
			{
			$sql = "DROP INDEX `{$indexName}` ON `{$table}`";
			}

		return $this->runSQL($sql);
		}

	/**
	 * Drops the primary key
	 */
	protected function dropPrimaryKey(string $table) : bool
		{
		$fields = \PHPFUI\ORM::describeTable($table);
		$indexes = \PHPFUI\ORM::getIndexes($table);

		foreach ($indexes as $index)
			{
			if ($index->primaryKey)
				{
				$sql = 'alter table ' . $table;

				$field = $fields[$index->name];

				if ($field->autoIncrement)
					{
					$nullable = $field->nullable ? '' : 'NOT NULL';
					$sql .= " change `{$field->name}` `{$field->name}` {$field->type} {$nullable},";
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
		return $this->runSQL("DROP TABLE IF EXISTS `{$table}`");
		}

	/**
	 * Drops tables contained in the array
	 *
	 * @param array<string> $tables
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
		return $this->runSQL("DROP VIEW IF EXISTS `{$view}`");
		}

	/**
	 * Drops views contained in the array
	 *
	 * @param array<string> $views
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
		$indexes = \PHPFUI\ORM::getIndexes($table);

		foreach ($indexes as $index)
			{
			if ($index->keyName === $indexName)
				{
				return true;
				}
			}

		return false;
		}

	/**
	 * Rename a column incluing
	 */
	protected function renameColumn(string $table, string $field, string $newName) : bool
		{
		$fieldInfo = $this->getFieldInfo($table, $field);

		if ($fieldInfo)
			{
			$sql = "RENAME COLUMN `{$field}` TO `{$newName}`";
			$this->addAlter($table, $sql);
			}

		return true;
		}

	/**
	 * Renames an existing table
	 */
	protected function renameTable(string $oldName, string $newName) : bool
		{
		$this->dropTable($newName);

		return $this->runSQL("rename table `{$oldName}` to `{$newName}`");
		}

	private function alter(string $type, string $table, string $field, string $extra = '') : void
		{
		$sql = $type . " COLUMN `{$field}`";

		if ('CHANGE' == $type)
			{
			$sql .= " `{$field}`";
			}

		if ('DROP' != $type)
			{
			$sql .= ' ' . $extra;
			}

		$this->addAlter($table, $sql);
		}

	private function addAlter(string $table, $sql) : void
		{
		if (! isset($this->alters[$table]))
			{
			$this->alters[$table] = [];
			}
		$this->alters[$table][] = $sql;
		}

	private function getFieldInfo(string $table, string $fieldName) : ?\PHPFUI\ORM\Schema\Field
		{
		$fields = \PHPFUI\ORM::describeTable($table);

		return $fields[$fieldName] ?? null;
		}
	}
