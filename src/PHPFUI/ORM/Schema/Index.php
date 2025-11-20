<?php

namespace PHPFUI\ORM\Schema;

class Index
	{
	public string $extra;

	public string $keyName;

	public string $name;

	public bool $primaryKey;

	/**
	 * @param array<string,mixed> $fields
	 */
	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $fields)
		{
		if (empty($fields))
			{
			return;
			}

		if (\str_starts_with($pdo->getDSN(), 'mysql'))
			{
			$this->primaryKey = 'PRIMARY' == $fields['Key_name'];
			$this->keyName = $fields['Key_name'];
			$this->name = $fields['Column_name'];
			$this->extra = \implode(',', $fields);
			}
		else
			{
			$this->name = $this->keyName = $fields['name'];
			$this->extra = $fields['sql'] ?? '';
			$this->primaryKey = false;
			}
		}
	}
