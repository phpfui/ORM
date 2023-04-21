<?php

namespace PHPFUI\ORM\Schema;

class Index
	{
	public readonly bool $primaryKey;

	public readonly string $name;

	public readonly string $extra;

	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $fields)
		{
		if (\str_starts_with($pdo->getDSN(), 'mysql'))
			{
			$this->primaryKey = 'PRIMARY' == $fields['Key_name'] || ! (bool)$fields['Non_unique'];
			$this->name = $fields['Column_name'];
			$this->extra = \implode(',', $fields);
			}
		else
			{
			$this->name = $fields['name']; /** @phpstan-ignore-line */
			$this->extra = $fields['sql'] ?? ''; /** @phpstan-ignore-line */
			$this->primaryKey = false; /** @phpstan-ignore-line */
			}
		}
	}
