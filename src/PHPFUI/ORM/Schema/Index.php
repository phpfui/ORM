<?php

namespace PHPFUI\ORM\Schema;

class Index
	{
	public readonly string $extra;

	public readonly string $name;

	public readonly bool $primaryKey;

	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $fields)
		{
		if (\str_starts_with($pdo->getDSN(), 'mysql'))
			{
			$this->primaryKey = 'PRIMARY' == $fields['Key_name'];
			$this->name = $fields['Column_name'];
			$this->extra = \implode(',', $fields);
			}
		else
			{
			$this->name = $fields['name'];
			$this->extra = $fields['sql'] ?? '';
			$this->primaryKey = false;
			}
		}
	}
