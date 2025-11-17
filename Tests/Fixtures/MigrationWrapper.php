<?php

namespace Tests\Fixtures;

class MigrationWrapper extends \PHPFUI\ORM\Migration
	{
	public function addColumnTest(string $table, string $field, string $parameters) : bool
		{
		return $this->addColumn($table, $field, $parameters);
		}

	/**
	 * @param array<string> $columns
	 */
	public function addForeignKeyTest(string $toTable, string $referenceTable, array $columns, string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE') : bool
		{
		return $this->addForeignKey($toTable, $referenceTable, $columns, $onDelete, $onUpdate);
		}

	/**
	 * @param array<string>|string $fields
	 */
	public function addIndexTest(string $table, string | array $fields, string $indexType = '') : bool
		{
		return $this->addIndex($table, $fields, $indexType);
		}

	public function addPrimaryKeyAutoIncrementTest(string $table, string $field = '', string $newFieldName = '') : bool
		{
		return $this->addPrimaryKeyAutoIncrement($table, $field, $newFieldName);
		}

	/**
	 * @param array<string> $fields
	 */
	public function addPrimaryKeyTest(string $table, array $fields) : bool
		{
		return $this->addPrimaryKey($table, $fields);
		}

	public function alterColumnTest(string $table, string $field, string $parameters) : bool
		{
		return $this->alterColumn($table, $field, $parameters);
		}

	public function renameColumnTest(string $table, string $field, string $newName) : bool
		{
		return $this->renameColumn($table, $field, $newName);
		}

	/**
	 * @param array<string> $keys
	 */
	public function deleteDuplicateRowsTest(string $table, array $keys) : bool
		{
		return $this->deleteDuplicateRows($table, $keys);
		}

	public function down() : bool
		{
		return true;
		}

	public function dropAllIndexesTest(string $table) : void
		{
		$this->dropAllIndexes($table);
		}

	public function dropColumnTest(string $table, string $field) : bool
		{
		return $this->dropColumn($table, $field);
		}

	/**
	 * @param array<string> | string $columns
	 */
	public function dropForeignKeyTest(string $table, string | array $columns) : bool
		{
		return $this->dropForeignKey($table, $columns);
		}

	/**
	 * @param array<string>|string $fields
	 */
	public function dropIndexTest(string $table, string | array $fields) : bool
		{
		return $this->dropIndex($table, $fields);
		}

	public function dropPrimaryKeyTest(string $table) : bool
		{
		return $this->dropPrimaryKey($table);
		}

	/**
	 * @param array<string> $tables
	 */
	public function dropTablesTest(array $tables) : void
		{
		$this->dropTables($tables);
		}

	public function dropTableTest(string $table) : bool
		{
		return $this->dropTable($table);
		}

	public function indexExistsTest(string $table, string $indexName) : bool
		{
		return $this->indexExists($table, $indexName);
		}

	public function renameTableTest(string $oldName, string $newName) : bool
		{
		return $this->renameTable($oldName, $newName);
		}

	public function up() : bool
		{
		return true;
		}
	}
