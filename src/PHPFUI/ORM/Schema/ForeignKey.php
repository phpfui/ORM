<?php

namespace PHPFUI\ORM\Schema;

class ForeignKey
	{
	public readonly string $constraintField;

	public readonly string $constraintTable;

	public readonly string $deleteRule;

	public readonly string $match;

	public readonly string $name;

	public readonly string $referencedField;

	public readonly string $referencedTable;

	public readonly string $updateRule;

	/**
	 * @param array<string> $row
	 */
	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $row)	// @phpstan-ignore-line
		{
		if ('PRIMARY' === ($row['from'] ?? 'PRIMARY'))
			{
			$class = \PHPFUI\ORM::$recordNamespace . '\\' . \PHPFUI\ORM::getBaseClassName($row['TABLE_NAME']);
			$row['from'] = $class::getPrimaryKeys()[0];
			}

		if ('PRIMARY' === ($row['to'] ?? 'PRIMARY'))
			{
			$class = \PHPFUI\ORM::$recordNamespace . '\\' . \PHPFUI\ORM::getBaseClassName($row['table']);
			$row['to'] = $class::getPrimaryKeys()[0];
			}
		$this->deleteRule = $row['on_delete'] ?? '';
		$this->updateRule = $row['on_update'] ?? '';
		$this->match = $row['match'] ?? '';
		$this->constraintTable = $row['TABLE_NAME'];
		$this->constraintField = $row['from'];
		$this->referencedTable = $row['table'];
		$this->referencedField = $row['to'];
		$this->name = $row['CONSTRAINT_NAME'] ?? 'fk_' . $this->referencedTable . '_' . $this->referencedField;
		}
	}
