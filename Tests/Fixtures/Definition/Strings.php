<?php

namespace Tests\Fixtures\Definition;

abstract class Strings extends \PHPFUI\ORM\Record
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
				'starts_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'ends_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'contains' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'istarts_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'iends_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'icontains' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_starts_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_ends_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_contains' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_istarts_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_iends_with' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_icontains' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
			];
			}

		return $this;
		}
}
