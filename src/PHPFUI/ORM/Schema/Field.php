<?php

namespace PHPFUI\ORM\Schema;

class Field
	{
	public readonly bool $autoIncrement;

	public readonly ?string $defaultValue;

	public readonly string $name;

	public readonly bool $nullable;

	public readonly bool $primaryKey;

	public readonly string $type;

	/**
	 * @param array<string,mixed> $fields
	 */
	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $fields, bool $autoIncrement)
		{
		if (\str_starts_with($pdo->getDSN(), 'mysql'))
			{
			$this->name = $fields['Field'];
			$this->type = \strtolower($fields['Type']);
			$this->nullable = 'YES' == $fields['Null'];
			$this->defaultValue = $fields['Default'];
			$this->primaryKey = false;	// use indexes to find primary keys
			$this->autoIncrement = \str_contains($fields['Extra'], 'auto_increment');

			return;
			}
		// SQLite
		$this->name = $fields['name'];
		$this->type = \strtolower($fields['type']);
		$this->nullable = ! (bool)$fields['notnull'];
		$this->defaultValue = $fields['dflt_value'];
		$this->primaryKey = (bool)$fields['pk'];
		$this->autoIncrement = $autoIncrement && $this->primaryKey;
		}
	}
