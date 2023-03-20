<?php

namespace PHPFUI\ORM;

/**
 * The Children class allows you to easily get the children records for any field of a **Record**.
 *
 * In the **Record** class definition, you need to define a virtual field with the name of the child relationship.  The key of the virtual field is the member name and the value is an array.
 *
 * The values in the array should be **\PHPFUI\ORM\Children::class** followed by the child table, then the optional parameters of an order by column and sort order (defaults to ASC).
 *
 * Example:
 * ```php
 * protected static array $virtualFields = [
 *   'OrderDetailChildren' => [\PHPFUI\ORM\Children::class, \Tests\App\Table\OrderDetail::class, 'data_allocated', 'desc'],
 * ];
 * ```
 */
class Children extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string, string[]> $parameters containing **\PHPFUI\ORM\Children::class** followed by the child table, then the optional parameters of an order by column and sort order (defaults to ASC).
	 */
	public function getValue(array $parameters) : mixed
		{
		$child = \array_shift($parameters);
		$childTable = new $child();
		$condition = new \PHPFUI\ORM\Condition();

		foreach ($this->parentRecord->getPrimaryKeys() as $primaryKey => $junk)
			{
			$condition->and($primaryKey, $this->parentRecord->{$primaryKey});
			}
		$childTable->setWhere($condition);

		$orderBy = \array_shift($parameters);
		$sort = \array_shift($parameters) ?? 'asc';

		if ($orderBy)
			{
			$childTable->addOrderBy($orderBy, $sort);
			}

		return $childTable->getRecordCursor();
		}
	}
