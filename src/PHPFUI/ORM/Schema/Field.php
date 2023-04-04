<?php

namespace PHPFUI\ORM\Schema;

class Field
	{
	public readonly string $name;

	public readonly string $type;

	public readonly bool $nullable;

	public readonly ?string $defaultValue;

	public readonly bool $primaryKey;

	public readonly bool $autoIncrement;

	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $fields, bool $autoIncrement)
		{
		if (\str_starts_with($pdo->getDSN(), 'mysql'))
			{
			$this->name = $fields['Field'];
			$this->type = \strtolower($fields['Type']);
			$this->nullable = 'YES' == $fields['Null'];
			$this->defaultValue = $fields['Default'];
			$this->primaryKey = 'PRI' == $fields['Key'];
			$this->autoIncrement = \str_contains($fields['Extra'], 'auto_increment');

			return;
			}
		// SQLite
		$this->name = $fields['name']; /** @phpstan-ignore-line */
		$this->type = \strtolower($fields['type']); /** @phpstan-ignore-line */
		$this->nullable = ! (bool)$fields['notnull']; /** @phpstan-ignore-line */
		$this->defaultValue = 'NULL' != $fields['dflt_value'] ? $fields['dflt_value'] : null; /** @phpstan-ignore-line */
		$this->primaryKey = (bool)$fields['pk']; /** @phpstan-ignore-line */
		$this->autoIncrement = $autoIncrement && $this->primaryKey; /** @phpstan-ignore-line */
		}
	}
