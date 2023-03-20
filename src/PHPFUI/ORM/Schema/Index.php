<?php

namespace PHPFUI\ORM\Schema;

class Index
	{
	public readonly string $name;

	public readonly string $extra;

//	public readonly bool $unique;

	public function __construct(\PHPFUI\ORM\PDOInstance $pdo, array $fields)
		{
		if (\str_starts_with($pdo->getDSN(), 'mysql'))
			{
			$this->name = $fields['Key_name'];
//			$this->unique = !(bool)$fields['Non_unique'];
			$this->extra = \implode(',', $fields);

			return;
			}

		$this->name = $fields['name']; /** @phpstan-ignore-line */
		$this->extra = $fields['sql']; /** @phpstan-ignore-line */
		}
	}
