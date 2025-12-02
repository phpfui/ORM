<?php

namespace PHPFUI\ORM\Schema;

class Field
	{
	public bool $autoIncrement;

	public ?string $defaultValue;

	public readonly string $extra;

	public readonly string $name;

	public readonly bool $nullable;

	public bool $primaryKey;

	public readonly string $type;

	/**
	 * @param array<string,mixed> $fields
	 */
	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $fields, bool $autoIncrement)
		{
		if (\str_starts_with($pdo->getDSN(), 'mysql') || $pdo->postGre)
			{
			$this->name = $fields['Field'];
			$this->type = \strtolower($fields['Type']);
			$this->nullable = 'YES' == $fields['Null'];
			$defaultValue = $fields['Default'];
			$doubleColon = $defaultValue ? \strpos($defaultValue, '::') : null;

			if ($doubleColon)
				{
				$defaultValue = \trim(\substr($defaultValue, 0, $doubleColon), "'");
				}

			if ('NULL' == $defaultValue)
				{
				$defaultValue = null;
				}
			$this->defaultValue = $defaultValue;

			if ('current_timestamp()' == $this->defaultValue)
				{
				$this->defaultValue = 'CURRENT_TIMESTAMP';
				}
			$this->primaryKey = false;
			$this->autoIncrement = \str_contains($fields['Extra'], 'auto_increment');
			$this->extra = \str_replace('auto_increment', '', $fields['Extra']);

			return;
			}
		// SQLite
		$this->name = $fields['name'];
		$this->type = \strtolower($fields['type']);
		$this->nullable = ! (bool)$fields['notnull'];
		$this->defaultValue = 'NULL' === $fields['dflt_value'] ? null : $fields['dflt_value'];
		$this->defaultValue = $this->defaultValue ? \trim($this->defaultValue, "'") : $this->defaultValue;
		$this->primaryKey = (bool)$fields['pk'];
		$this->autoIncrement = $autoIncrement && $this->primaryKey;
		$this->extra = '';
		}
	}
