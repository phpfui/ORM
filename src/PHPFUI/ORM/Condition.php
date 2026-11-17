<?php

namespace PHPFUI\ORM;

/**
 * Conditions are used for the WHERE, HAVING and JOIN conditions of the query. Think of each Condition as enclosed in parentheses ().
 *
 * You start with an initial test: Field Value Operator (FVO) tupple.
 *
 * You can then add additional FVO tupples with a logical operator (AND, OR, AND NOT, OR NOT) separating the previous FVO tupple.
 *
 * To add a sub condition in parentheses, add another Condition with the same logical operator separator.
 *
 * You can use a **\PHPFUI\ORM\Table** as either the field or value that will resolve to a select statement. It is your responibility to insure the select statement produces valid SQL for a sub query.
 */
class Condition implements \Countable, \Stringable
	{
	/** @var array<array<mixed>> $conditions */
	private array $conditions = [];

	/**
	 * Start a Condition with a Field Value Operator (FVO) tupple.
	 *
	 * Will try to parse FVO from string if $operator is null.
	 *
	 * @param string|\PHPFUI\ORM\Literal|\PHPFUI\ORM\Table|null $field single name (no .) of a field existing the the table.  Will try to parse FVO from string if $operator is null.
	 * @param mixed $value to test field against.  Must be string for LIKE operators and an array for IN operators. Can also be a **\PHPFUI\ORM\Table** that will evaluate to a correct sub query.
	 * @param \PHPFUI\ORM\Operator $operator comparision of your choice
	 */
	public function __construct(string|\PHPFUI\ORM\Literal|\PHPFUI\ORM\Table|null $field = null, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal())
		{
		if ($field)
			{
			$this->add('', $field, $operator, $value);
			}
		}

	/**
	 * @return string  of condition with values replaced by ? for PDO
	 */
	public function __toString() : string
		{
		$retVal = '';
		$first = '';
		foreach ($this->conditions as $parts)
			{
			if ($parts[1] instanceof \PHPFUI\ORM\Table)
				{
				$parts[1] = new \PHPFUI\ORM\Literal("({$parts[1]->getSelectSQL()})");
				}

			if (! \is_object($parts[1]) || $parts[1] instanceof \PHPFUI\ORM\Literal)
				{
				if ($parts[0])
					{
					$retVal .= " {$parts[0]}";
					}
				$field = $parts[1];
				$value = $parts[3];
				$operator = " {$parts[2]}";

				if ($parts[2]->correctlyTyped($value))
					{
					if ($field instanceof \PHPFUI\ORM\Literal)
						{
						$escapedField = (string)$field;
						}
					else
						{
						$escapedField = '`' . \str_replace('.', '`.`', (string)$field) . '`';
						}
					$retVal .= "{$first}{$escapedField}{$operator} ";
					$first = ' ';

					if (\is_array($value))
						{
						$count = \count($value);

						if ($count)
							{
							$retVal .= '(' . \implode(',', \array_fill(0, $count, '?')) . ')';
							}
						else	// in clause with empty set, can not be a match, return false
							{
							$retVal = 'FALSE';
							}
						}
					elseif ($value instanceof \PHPFUI\ORM\Table)
						{
						$input = [];
						$retVal .= '(' . $value->getSelectSQL($input) . ')';
						}
					elseif (null !== $value)
						{
						$retVal .= '?';
						}
					}
				elseif (\is_object($value))
					{
					if ($value instanceof \PHPFUI\ORM\Table)
						{
						$input = [];
						$retVal .= "{$first}{$field}{$operator} (" . $value->getSelectSQL($input) . ')';
						}
					else
						{
						$retVal .= "{$first}{$field}{$operator} {$value}";
						}
					$first = ' ';
					}
				else
					{
					$type = \gettype($value);

					throw new \PHPFUI\ORM\Exception("{$field} has incorrect type ({$type}) for {$operator}");
					}
				}
			else
				{
				$retVal .= " {$parts[0]} ({$parts[1]})";
				}

			}

		return $retVal;
		}

	/**
	 * Add logical AND between FVO tupples or Condition
	 */
	public function and(string | \PHPFUI\ORM\Condition | \PHPFUI\ORM\Literal $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('AND', $condition, $operator, $value);
		}

	/**
	 * Add logical AND NOT between FVO tupples or Condition
	 */
	public function andNot(string | \PHPFUI\ORM\Condition | \PHPFUI\ORM\Literal $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('AND NOT', $condition, $operator, $value);
		}

	/**
	 * @return int  the number of FVO tupples in the condition
	 */
	public function count() : int
		{
		return \count($this->conditions);
		}

	/**
	 * @return array<string> of all the fields used by the condition
	 */
	public function getFields() : array
		{
		$retVal = [];

		foreach ($this->conditions as $parts)
			{
			if (! \is_object($parts[1]))
				{
				$retVal[] = $parts[1];
				}
			else
				{
				$retVal = \array_merge($retVal, $parts[1]->getFields());
				}
			}

		return $retVal;
		}

	/**
	 * @return string[]  of values that will match the ? returned in the condition string for PDO execution
	 */
	public function getInput() : array
		{
		$retVal = [];

		foreach ($this->conditions as $parts)
			{
			foreach ($parts as $i => $part)
				{
				if ($part instanceof \PHPFUI\ORM\Table)
					{
					$subQueryInput = [];
					$part->getSelectSQL($subQueryInput);
					$retVal = \array_merge($retVal, $subQueryInput);
					}
				elseif ($part instanceof \PHPFUI\ORM\Condition)
					{
					$retVal = \array_merge($retVal, $part->getInput());
					}
				elseif (is_array($part))
					{
					$retVal = \array_merge($retVal, $part);
					}
				elseif ($i === 3 && ! is_object($part))
					{
					if ($parts[2]->hasParameter())
						{
						$retVal[] = $part;
						}
					}
				}
			}

		return $retVal;
		}

	public function getJSON() : string
		{
		return \json_encode($this->getConditionArray($this->conditions), JSON_THROW_ON_ERROR);
		}

	/**
	 * Add logical OR between FVO tupples or Condition
	 */
	public function or(string | \PHPFUI\ORM\Condition | \PHPFUI\ORM\Literal $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('OR', $condition, $operator, $value);
		}

	/**
	 * Add logical OR NOT between FVO tupples or Condition
	 */
	public function orNot(string | \PHPFUI\ORM\Condition | \PHPFUI\ORM\Literal $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('OR NOT', $condition, $operator, $value);
		}

	/**
	 * Internal method to type check $condition parameter
	 */
	private function add(string $logical, string | \PHPFUI\ORM\Condition | \PHPFUI\ORM\Literal | \PHPFUI\ORM\Table $condition, \PHPFUI\ORM\Operator $operator, mixed $value) : static
		{
		// convert enum to the backed type
		if (\is_object($value) && \enum_exists($value::class))
			{
			$value = $value->value;
			}

		if (null === $value && ! $operator->correctlyTyped($value))
			{
			if ($operator instanceof \PHPFUI\ORM\Operator\Equal)
				{
				$operator = new \PHPFUI\ORM\Operator\IsNull();
				}
			else
				{
				$operator = new \PHPFUI\ORM\Operator\IsNotNull();
				}
			}

		if (empty($this->conditions))
			{
			$logical = '';
			}

		if ($condition instanceof \PHPFUI\ORM\Table)
			{
			$this->conditions[] = [$logical, $condition, $operator, $value];
			}
		elseif ('string' == \gettype($condition) || \PHPFUI\ORM\Literal::class == $condition::class)
			{
			$this->conditions[] = [$logical, $condition, $operator, $value];
			}
		elseif (self::class == $condition::class)
			{
			$this->conditions[] = [$logical, $condition];
			}
		else
			{
			throw new \PHPFUI\ORM\Exception('Invalid type in ' . self::class);
			}

		return $this;
		}

	/**
	 * @param array<array<mixed>> $conditions
	 *
	 * @return array<array<mixed>>
	 */
	private function getConditionArray(array $conditions) : array
		{
		$data = [];

		foreach ($conditions as $condition)
			{
			if (4 == (\is_countable($condition) ? \count($condition) : 0)) // @phpstan-ignore-line
				{
				// convert operator to string
				$condition[2] = $condition[2]->getOperatorString();
				$data[] = $condition;
				}
			else
				{
				$condition[1] = $this->getConditionArray($condition[1]->conditions);
				$data[] = $condition;
				}
			}

		return $data;
		}
	}
