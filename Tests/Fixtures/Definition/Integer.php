<?php

namespace Tests\Fixtures\Definition;

abstract class Integer extends \PHPFUI\ORM\Record
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
				'integer' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'int', 19, false, '', false, ),
				'not_integer' => new \PHPFUI\ORM\FieldDefinition('!sqltype', 'null', 19, false, '', false, ),
			];
			}

		return $this;
		}
}
