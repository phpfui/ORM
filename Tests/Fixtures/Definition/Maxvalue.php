<?php

namespace Tests\Fixtures\Definition;

abstract class Maxvalue extends \PHPFUI\ORM\Record
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
				'maxvalue' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 19, false, '', false, ),
				'not_maxvalue' => new \PHPFUI\ORM\FieldDefinition('!sqltype', 'string', 19, false, '', false, ),
				'value' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 19, false, '', false, ),
			];
			}

		return $this;
		}
}
