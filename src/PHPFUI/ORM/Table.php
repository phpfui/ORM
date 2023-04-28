<?php

namespace PHPFUI\ORM;

abstract class Table implements \Countable
	{
	protected static string $className = '';

	protected string $distinct = '';

	protected array $groupBys = [];

	protected ?\PHPFUI\ORM\Condition $havingCondition = null;

	protected \PHPFUI\ORM\Record $instance;

	protected array $joins = [];

	protected array $lastInput = [];

	protected string $lastSql = '';

	protected ?int $limit = null;

	protected ?int $offset = null;

	protected array $orderBys = [];

	protected ?int $page = null;

	protected array $selects = [];

	protected array $unions = [];

	protected ?\PHPFUI\ORM\Condition $whereCondition = null;

	private bool $fullJoinSelects = false;

	private static $translationCallback = null;

	public function __construct()
		{
		$this->instance = new static::$className();
		}

	public function addFind(array $parameters) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->lastInput = [];

		$fields = $this->getFields();

		$condition = $this->getWhereCondition();

		foreach ($parameters as $field => $value)
			{
			$baseField = $field;
			$parts = \explode(':', $field);
			$direction = '';

			if (\count($parts) > 1)
				{
				$baseField = \array_shift($parts);
				$direction = \array_shift($parts);
				}

			if (! isset($fields[$baseField]) || '' === $value)
				{
				continue;
				}
			$type = $fields[$baseField][\PHPFUI\ORM\Record::PHP_TYPE_INDEX] ?? 'string';

			if (\in_array($type, ['int', 'float', 'string', 'timestamp']))
				{
				if ($direction)
					{
					$condition->and($baseField, $parameters[$field], 'min' == $direction ? new \PHPFUI\ORM\Operator\GreaterThanEqual() : new \PHPFUI\ORM\Operator\LessThanEqual());
					}
				elseif ($parameters[$field])
					{
					$condition->and($baseField, $parameters[$field]);
					}
				}
			elseif ('string' == $type && $value)
				{
				$condition->and($baseField, '%' . $parameters[$field] . '%', new \PHPFUI\ORM\Operator\Like());
				}
			}
		$this->setWhere($condition);

		if (isset($parameters['p']))
			{
			if (isset($parameters['l']))
				{
				$this->limit = (int)$parameters['l'];
				}
			$this->page = (int)$parameters['p'];
			}

		if (isset($parameters['c'], $this->getFields()[$parameters['c']]))
			{
			$this->addOrderBy($parameters['c'], $parameters['s'] ?? 'a');
			}

		return $this->getDataObjectCursor();
		}

	/**
	 * Add a valid group field
	 *
	 * @param bool $rollup can be applied to any group by field, but affects the entire group by clause
	 */
	public function addGroupBy(string $field, bool $rollup = false) : static
		{
		if (\strlen($field))
			{
			$this->groupBys[$field] = $rollup;
			}

		return $this;
		}

	/**
	 * Add a join with another table
	 *
	 * @param string $table name of the table to join, case sensitive
	 * @param string | \PHPFUI\ORM\Condition $on condition.  If string, name of field on the $table.  Defaults to table name appended with Id. Or \PHPFUI\ORM\Condition for complex joins
	 * @param string $type of join
	 */
	public function addJoin(string $table, string | \PHPFUI\ORM\Condition $on = '', string $type = 'LEFT', string $as = '') : static
		{
		$ucfTable = \PHPFUI\ORM::getBaseClassName($table);
		$joinTableClass = '\\' . \PHPFUI\ORM::$tableNamespace . "\\{$ucfTable}";

		if (! \class_exists($joinTableClass))
			{
			throw new \PHPFUI\ORM\Exception("Table {$table} was not found");
			}

		$type = \strtoupper($type);
		$validJoins = ['LEFT' => true, 'INNER' => true, 'OUTER' => true, 'RIGHT' => true, 'FULL' => true, 'CROSS' => true];

		foreach (\explode(' ', $type) as $joinType)
			{
			if (! isset($validJoins[$joinType]))
				{
				throw new \PHPFUI\ORM\Exception("Join {$joinType} is not valid");
				}
			}

		$joinTable = new $joinTableClass();

		if (\is_string($on))
			{
			$joinField = $table . \PHPFUI\ORM::$idSuffix;

			if (empty($on))
				{
				$on = $joinField;
				}
			$onCondition = new \PHPFUI\ORM\Condition(new \PHPFUI\ORM\Field($table . '.' . $on), new \PHPFUI\ORM\Field($this->getTableName() . '.' . $on));
			}
		else
			{
			$onCondition = $on;
			}

		if ($as)
			{
			$as = '~ AS ' . $as;
			}
		$this->joins[$joinTable->getTableName() . $as] = [$joinTable, $onCondition, $type];

		return $this;
		}

	public function addOrderBy(string $field, string $ascending = 'ASC') : static
		{
		if (\strlen($field))
			{
			$this->orderBys[$field] = ! \str_contains(\strtoupper($ascending), 'D') ? 'ASC' : 'DESC';
			}

		return $this;
		}

	/**
	 * Add a field the the select, must be a valid field
	 */
	public function addSelect(string | object $field, string $as = '') : static
		{
		if (\is_object($field))
			{
			$this->selects["{$field}"] = $as;
			}
		else
			{
			$parts = \explode('.', $field);
			$field = \implode('`.`', $parts);
			$this->selects['`' . $field . '`'] = $as;
			}

		return $this;
		}

	/**
	 * Add table for union.
	 *
	 * @param bool $any if true, adds all records from query, defaults to distinct records only
	 */
	public function addUnion(\PHPFUI\ORM\Table $table, bool $any = false) : static
		{
		$this->unions[] = [$table, $any];

		return $this;
		}

	/**
	 * Split a string into words based on capital letters. Successive capital letters are considered an appreviation and grouped together.
	 */
	public static function capitalSplit(string $key) : string
		{
		$len = \strlen($key);
		$space = $output = '';
		$lastCapitalized = false;
		$consecutiveCaps = 0;

		for ($i = 0; $i < $len; ++$i)
			{
			$char = $key[$i];

			if (0 == $i)
				{
				$char = \strtoupper($char);
				}

			if (\ctype_upper($char))
				{
				if (! $lastCapitalized)
					{
					$output .= $space;
					}
				++$consecutiveCaps;
				$space = ' ';
				$lastCapitalized = true;
				}
			elseif ($lastCapitalized)
				{
				$length = \strlen($output);

				if ($length > 1 && $consecutiveCaps > 1)
					{
					$output = \substr($output, 0, $length - 1) . ' ' . $output[$length - 1];
					}
				$consecutiveCaps = 0;
				$lastCapitalized = false;
				}
			$output .= $char;
			}

		return $output;
		}

	/**
	 * Returns the count for the limited query.
	 */
	public function count() : int
		{
		$input = [];
		$sql = $this->getCountSQL($input);

		return (int)\PHPFUI\ORM::getValue($sql, $input);
		}

	/**
	 * Delete record matching the requested parameters
	 */
	public function delete(bool $allowDeleteAll = false) : static
		{
		$table = $this->instance->getTableName();

		if (! $allowDeleteAll && null === $this->whereCondition)
			{
			throw new \PHPFUI\ORM\Exception('Delete all records is NOT allowed for table ' . $table);
			}

		$this->lastInput = [];
		$where = $this->getWhere($this->lastInput);
		$limit = $this->getLimitClause();
		$this->lastSql = "DELETE FROM `{$table}`{$where}{$limit}";
		\PHPFUI\ORM::execute($this->lastSql, $this->lastInput);

		return $this;
		}

	/**
	 * transform any field or table.field from join
	 */
	public function displayTransform(string $field, $value = null) : mixed
		{
		$parts = \explode('_', $field);

		if (2 <= \count($parts))
			{
			$field = $parts[1];

			if (isset($this->joins[$parts[0]]))
				{
				$joinedTable = $this->joins[$parts[0]][0];

				return $joinedTable->displayTransform($field, $value);
				}
			}

		return $this->instance->displayTransform($field, $value);
		}

	public function find(array $parameters) : \PHPFUI\ORM\DataObjectCursor
		{
		// reset find condition
		$this->whereCondition = new \PHPFUI\ORM\Condition();

		return $this->addFind($parameters);
		}

	/**
	 * Get all tables in the application
	 */
	public static function getAllTables(array $skipTables = []) : array
		{
		$iterator = new \DirectoryIterator(\PHPFUI\ORM::getTableNamespacePath());
		$currentTables = [];

		foreach ($iterator as $item)
			{
			if ($item->isFile())
				{
				$fileName = $item->getFilename();
				$tableName = \str_replace('.php', '', $fileName);

				if (\in_array($tableName, $skipTables))
					{
					continue;
					}

				$className = '\\' . \PHPFUI\ORM::$tableNamespace . "\\{$tableName}";
				$currentTables[$className] = new $className();
				}
			}

		return $currentTables;
		}

	/**
	 * Return a array collection matching the requested parameters
	 */
	public function getArrayCursor() : \PHPFUI\ORM\ArrayCursor
		{
		$this->lastInput = [];
		$this->lastSql = $this->getSQL($this->lastInput);

		$totalInput = [];

		return \PHPFUI\ORM::getArrayCursor($this->lastSql, $this->lastInput)->setCountSQL($this->getCountSQL($totalInput))->setTotalCountSQL($this->getTotalSQL($totalInput));
		}

	/**
	 * Return a object collection matching the requested parameters
	 */
	public function getDataObjectCursor() : \PHPFUI\ORM\DataObjectCursor
		{
		$this->lastInput = [];
		$this->lastSql = $this->getSQL($this->lastInput);
		$totalInput = [];

		return \PHPFUI\ORM::getDataObjectCursor($this->lastSql, $this->lastInput)->setCountSQL($this->getCountSQL($totalInput))->setTotalCountSQL($this->getTotalSQL($totalInput));
		}

	public function getFields() : array
		{
		return $this->instance->getFields();
		}

	/**
	 * @return string  the current group by string
	 */
	public function getGroupBy() : string
		{
		if (! $this->groupBys)
			{
			return '';
			}

		$comma = '';
		$retVal = ' GROUP BY';
		$addRollup = 0;

		foreach ($this->groupBys as $field => $rollup)
			{
			$parts = \explode('.', $field);
			$field = \implode('`.`', $parts);
			$retVal .= "{$comma} `{$field}`";
			$comma = ',';

			$addRollup |= (int)$rollup;
			}

		if ($addRollup)
			{
			$retVal .= ' WITH ROLLUP';
			}

		return $retVal;
		}

	/**
	 * Return the string starting with "having" for the query
	 *
	 * @param  array  $input array reference. Current contents will remain, and new contents appended to the array
	 *
	 * @return string " HAVING condition"
	 */
	public function getHaving(array &$input) : string
		{
		if (null === $this->havingCondition || ! \count($this->havingCondition))
			{
			return '';
			}

		$input = \array_merge($input, $this->havingCondition->getInput());

		return ' HAVING ' . $this->havingCondition;
		}

	public function getHavingCondition() : \PHPFUI\ORM\Condition
		{
		if (! $this->havingCondition)
			{
			$this->havingCondition = new \PHPFUI\ORM\Condition();
			}

		return $this->havingCondition;
		}

	public function getLastInput() : array
		{
		return $this->lastInput;
		}

	public function getLastSql() : string
		{
		return $this->lastSql;
		}

	/**
	 * @return ?int the current limit
	 */
	public function getLimit() : ?int
		{
		return $this->limit;
		}

	/**
	 * @return string  the current limit string
	 */
	public function getLimitClause() : string
		{
		// could just be a string, return it
		if ($this->limit && ! $this->offset && null === $this->page)
			{
			return ' LIMIT ' . $this->limit;
			}

		if (! $this->limit)
			{
			return '';
			}

		$offset = 0;

		if (null !== $this->offset)
			{
			$offset = $this->offset;
			}
		elseif (null !== $this->page)
			{
			$offset = $this->page * $this->limit;
			}
		$this->offset = $offset;

		return " LIMIT {$offset}, {$this->limit}";
		}

	public function getOffset() : ?int
		{
		return $this->offset;
		}

	/**
	 * @return string  the current order by string
	 */
	public function getOrderBy() : string
		{
		if (! $this->orderBys)
			{
			return '';
			}

		$comma = '';
		$retVal = ' ORDER BY';

		foreach ($this->orderBys as $field => $direction)
			{
			$parts = \explode('.', $field);
			$field = \implode('`.`', $parts);
			$retVal .= "{$comma} `{$field}` {$direction}";
			$comma = ',';
			}

		return $retVal;
		}

	public function getPage() : int
		{
		return (int)$this->page;
		}

	public function getPrimaryKeys() : array
		{
		return $this->instance->getPrimaryKeys();
		}

	public function getRecord() : \PHPFUI\ORM\Record
		{
		return clone $this->instance;
		}

	/**
	 * Return a Record collection matching the requested parameters
	 */
	public function getRecordCursor() : \PHPFUI\ORM\RecordCursor
		{
		$this->lastInput = [];
		$this->lastSql = $this->getSQL($this->lastInput);

		$totalInput = [];

		return \PHPFUI\ORM::getRecordCursor($this->instance, $this->lastSql, $this->lastInput)->setCountSQL($this->getCountSQL($totalInput))->setTotalCountSQL($this->getTotalSQL($totalInput));
		}

	/**
	 * Return a array of record matching the requested parameters
	 */
	public function getRows() : array
		{
		$this->lastInput = [];
		$this->lastSql = $this->getSQL($this->lastInput);

		return \PHPFUI\ORM::getRows($this->lastSql, $this->lastInput);
		}

	/**
	 * @return string  the current select string, '*' if nothing specified, or a comma delimited field list
	 */
	public function getSelect() : string
		{
		$sql = '';
		$comma = '';

		if (! $this->selects || $this->fullJoinSelects)
			{
			if (! $this->joins)
				{
				return '*';
				}

			$columns = [];
			// make explicit column names for joined tables since we don't have explicit selects
			$sql = "`{$this->instance->getTableName()}`.*";
			// set column names from explicit select
			foreach ($this->getFields() as $field => $data)
				{
				$columns[$field] = true;
				}

			foreach ($this->joins as $tableName => $joinInfo)
				{
				if (\str_contains($tableName, '~'))
					{
					[$tableName, $as] = \explode('~', $tableName);
					}

				foreach ($joinInfo[0]->getFields()as $field => $info)
					{
					if (isset($columns[$field]))
						{
						$sql .= ",`{$tableName}`.`{$field}` as `{$tableName}_{$field}`";
						}
					else
						{
						$sql .= ",`{$tableName}`.`{$field}`";
						$columns[$field] = true;
						}
					}
				}

			if (! $this->fullJoinSelects)
				{
				return $sql;
				}
			$comma = ',';
			}

		foreach ($this->selects as $field => $as)
			{
			$sql .= $comma . $field;

			if ($as)
				{
				$sql .= ' as `' . $as . '`';
				}
			$comma = ',';
			}
		$sql = \str_replace('`*`', '*', $sql);

		return $sql;
		}

	/**
	 * Sets up lastSql and lastInput variable for use in returning cursors
	 */
	public function getSQL(array &$input, bool $limited = true) : string
		{
		$table = $this->instance->getTableName();
		$select = $this->getSelect();
		$joins = $this->getJoins($input);
		$where = $this->getWhere($input);
		$groupBy = $this->getGroupBy();
		$having = $this->getHaving($input);
		$orderBy = $this->getOrderBy();
		$limit = $this->getLimitClause();
		$sql = "SELECT {$this->distinct} {$select} FROM `{$table}`" . $joins . $where . $groupBy . $having;

		if ($this->unions)
			{
			foreach ($this->unions as $info)
				{
				[$table, $any] = $info;
				$sql .= ' UNION ';

				if ($any)
					{
					$sql .= 'ANY ';
					}
				$sql .= ' ' . $table->getSQL($input, $limited) . ' ';
				}
			}
		$sql .= $orderBy;

		if ($limited)
			{
			$sql .= $limit;
			}

		return \str_replace('  ', ' ', \trim($sql));
		}

	public function getTableName() : string
		{
		return $this->instance->getTableName();
		}

	/**
	 * Return the string starting with "where" for the query
	 *
	 * @param  array  $input array reference. Current contents will remain, and new contents appended to the array
	 *
	 * @return string " where condition"
	 */
	public function getWhere(array &$input) : string
		{
		if (null === $this->whereCondition || ! \count($this->whereCondition))
			{
			return '';
			}

		$input = \array_merge($input, $this->whereCondition->getInput());

		return ' WHERE ' . $this->whereCondition;
		}

	public function getWhereCondition() : \PHPFUI\ORM\Condition
		{
		if (! $this->whereCondition)
			{
			$this->whereCondition = new \PHPFUI\ORM\Condition();
			}

		return $this->whereCondition;
		}

	public function setDistinct(string $distinct = 'DISTINCT') : static
		{
		$this->distinct = $distinct;

		return $this;
		}

	public function setFullJoinSelects(bool $fullSelects = true) : static
		{
		$this->fullJoinSelects = $fullSelects;

		return $this;
		}

	/**
	 * Reset to this group by field
	 *
	 * @param bool $rollup can be applied to any group by field, but affects the entire group by clause
	 */
	public function setGroupBy(string $field, bool $rollup = false) : self
		{
		$this->groupBys = [];

		return $this->addGroupBy($field, $rollup);
		}

	public function setHaving(?\PHPFUI\ORM\Condition $condition = null) : static
		{
		$this->havingCondition = $condition;

		return $this;
		}

	/**
	 * @param int $page is zero based, so 0 is the first page, 1 is the second page
	 */
	public function setLimit(int $limit = 20, ?int $page = null) : static
		{
		$this->limit = $limit;
		$this->page = $page;

		return $this;
		}

	public function setOffset(int $offset) : static
		{
		$this->offset = $offset;

		return $this;
		}

	public function setOrderBy(string $field, string $ascending = 'ASC') : self
		{
		$this->orderBys = [];

		return $this->addOrderBy($field, $ascending);
		}

	public function setSelectFields(string $clause) : static
		{
		$fields = \explode(',', $clause);

		// reconcatinate any fields with functions in them
		$finalFields = [];
		$finalField = '';
		$openParenCount = 0;

		foreach ($fields as $field)
			{
			if ($openParenCount)
				{
				$finalField .= ',' . $field;
				}
			else
				{
				$finalField = $field;
				}
			$openParenCount += \substr_count($field, '(');
			$openParenCount -= \substr_count($field, ')');

			if (! $openParenCount)
				{
				$finalFields[] = $finalField;
				}
			}

		foreach ($finalFields as $field)
			{
			$field = \trim($field);

			if (\stripos($field, ' as '))
				{
				$field = \str_ireplace(' as ', ' as ', $field);
				$parts = \explode(' as ', $field);
				$this->addSelect($parts[0], $parts[1]);
				}
			}

		return $this;
		}

	public static function setTranslationCallback(callable $callback) : void
		{
		self::$translationCallback = $callback;
		}

	public function setWhere(?\PHPFUI\ORM\Condition $condition = null) : static
		{
		$this->whereCondition = $condition;

		return $this;
		}

	/**
	 * Returns the total count for the unlimited query.
	 */
	public function total() : int
		{
		$input = [];
		$sql = $this->getTotalSQL($input);

		return (int)\PHPFUI\ORM::getValue($sql, $input);
		}

	/**
	 * Translate any valid field. $field must be a valid field, or empty to return the translated table name. Joined fields should be specified as table.field.
	 */
	public function translate(string $field = '') : string
		{
		if (empty($field))
			{
			return $this->doTranslation($this->instance->getTableName());
			}

		$parts = \explode('_', $field);

		if (2 <= \count($parts))
			{
			if (isset($this->joins[$parts[0]]))
				{
				$joinedTable = $this->joins[$parts[0]][0];

				return $joinedTable->translate() . ' ' . $joinedTable->translate($parts[1]);
				}
			$field = $parts[1];
			}

		return $this->doTranslation($field);
		}

	/**
	 * Update all record matching the requested parameters with the variables passed
	 *
	 * @param array $variables key => value array of variables to set
	 */
	public function update(array $variables) : static
		{
		$this->lastSql = 'UPDATE ' . $this->instance->getTableName() . ' SET';
		$comma = '';
		$this->lastInput = [];

		foreach ($variables as $field => $value)
			{
			$this->lastSql .= "{$comma} `{$field}`=?";
			$this->lastInput[] = $value;
			$comma = ',';
			}

		$where = $this->getWhere($this->lastInput);
		$limit = $this->getLimitClause();

		$this->lastSql .= $where . $limit;
		\PHPFUI\ORM::execute($this->lastSql, $this->lastInput);

		return $this;
		}

	public function updateFromTable(array $request) : bool
		{
		$fields = $this->instance->getFields();

		$primaryKeys = $this->getPrimaryKeys();

		$transation = new \PHPFUI\ORM\Transaction();

		if (\count($primaryKeys))
			{
			$mainKey = \array_key_first($primaryKeys);

			foreach ($request[$mainKey] ?? [] as $existingKey => $index)
				{
				$data = [];

				foreach ($fields as $field => $typeInfo)
					{
					if (isset($request[$field]))
						{
						if (\is_array($request[$field]))
							{
							$data[$field] = $request[$field][$index];
							}
						else
							{
							$data[$field] = $request[$field];
							}
						}
					}
				$this->instance->setEmpty()->setFrom($data)->insertOrUpdate();
				}
			}

		return $transation->commit();
		}

	private function doTranslation(string $text) : string
		{
		$translationCallback = null;

		if (self::$translationCallback)
			{
			return self::$translationCallback($text);
			}

		$parts = \explode('_', $text);

		foreach ($parts as $index => $part)
			{
			$parts[$index] = \PHPFUI\ORM::getBaseClassName($part);
			}

		return self::capitalSplit(\implode('', $parts));
		}

	private function getCountSQL(array &$input) : string
		{
		return 'SELECT COUNT(*) from (' . $this->getSql($input) . ') countAlias';
		}

	private function getJoins(array &$input) : string
		{
		$joins = '';

		foreach ($this->joins as $joinTableName => $joinInfo)
			{
			$as = '';

			if (\str_contains($joinTableName, '~'))
				{
				[$joinTableName, $as] = \explode('~', $joinTableName);
				}
			$onCondition = $joinInfo[1];
			$joinType = $joinInfo[2];
			$input = \array_merge($input, $onCondition->getInput());
			$joins .= " {$joinType} JOIN `{$joinTableName}`{$as} ON {$onCondition}";
			}

		return $joins;
		}

	private function getTotalSQL(array &$input) : string
		{
		$input = [];

		return 'SELECT COUNT(*) from (' . $this->getSql($input, false) . ') countAlias';
		}
	}
