<?php

namespace PHPFUI\ORM\Tool\Generate;

abstract class Base
	{
	abstract public function generate(string $table) : bool;

	public function nameSort(\PHPFUI\ORM\Schema\Field $lhs, \PHPFUI\ORM\Schema\Field $rhs) : int
		{
		return $lhs->name <=> $rhs->name;
		}

	abstract protected function getFieldDefinition(\PHPFUI\ORM\Schema\Field $field) : string;

	/**
	 * @return array<string, string>
	 */
	protected function getPrimaryKeys(string $table) : array
		{
		$primaryKeys = [];
		$fields = \PHPFUI\ORM::describeTable($table);

		foreach ($fields as $field)
			{
			if ($field->primaryKey)
				{
				$primaryKeys[$field->name] = $field->name;
				}
			}

		if (! $primaryKeys) // look in indicies if no primary, could be a composite primary key
			{
			$indexes = \PHPFUI\ORM::getIndexes($table);

			foreach ($indexes as $index)
				{
				if ($index->primaryKey)
					{
					$primaryKeys[$index->name] = $index->name;
					}
				}
			}

		return $primaryKeys;
		}

	protected function getTypeLength(string &$type) : float
		{
		$start = \strpos($type, '(');
		$precision = 0;

		if (false !== $start)
			{
			$precision = \rtrim(\substr($type, $start + 1), ')');

			if (\str_contains($precision, ','))
				{
				$parts = \explode(',', $precision);
				$precision = ((int)$parts[0]) + 1;
				}
			$type = \substr($type, 0, $start);
			}
		$type = \strtolower($type);

		switch ($type)
			{
			case 'timestamp':
			case 'datetime':
				$precision = 20;

				break;

			case 'date':
				$precision = 10;

				break;

			case 'tinytext':
				$precision = 256;

				break;

			case 'mediumtext':
				$precision = 16777215;

				break;

			case 'text':
				$precision = 65535;

				break;

			case 'longtext':
				$precision = 4294967295;

				break;
			}

		static $types = [
			'integer' => 'int',
			'int' => 'int',
			'int unsigned' => 'int',
			'smallint' => 'int',
			'tinyint' => 'int',
			'mediumint' => 'int',
			'bigint' => 'int',
			'smallserial' => 'int',
			'serial' => 'int',
			'bigserial' => 'int',
			'decimal' => 'float',
			'numeric' => 'float',
			'float' => 'float',
			'double' => 'float',
			'real' => 'float',
			'double precision' => 'float',
			'money' => 'float',
			'bit' => 'bool',
			'boolean' => 'bool',
			'year' => 'int',
		];
		$type = $types[$type] ?? 'string';

		return (float)$precision;
		}

	protected function line(float | int | string $field) : string
		{
		return "{$field}, ";
		}

	protected function quote(string $field) : string
		{
		return "'{$field}'";
		}

	protected function quoteLine(string $field) : string
		{
		return "'{$field}', ";
		}
	}
