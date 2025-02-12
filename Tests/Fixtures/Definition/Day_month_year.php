<?php

namespace Tests\Fixtures\Definition;

abstract class Day_month_year extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [];

	public static string $primaryKey = '';

	public static string $table = '';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'day_month_year' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 19, false, '', false, ),
				'not_day_month_year' => new \PHPFUI\ORM\FieldDefinition('!sqltype', 'string', 19, false, '', false, ),
			];
			}

		return $this;
		}
}
