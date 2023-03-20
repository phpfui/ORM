<?php

namespace PHPFUI\ORM;

/**
 * Conditions are used for the WHERE part of the query. Think of each Condition as enclosed in parentheses ().
 *
 * You start with an initial test: Field Operator Value (FOV) tupple.
 *
 * You can then add additional FOV tupples with a logical operator (AND, OR, AND NOT, OR NOT) separating the previous FOV tupple.
 *
 * To add a sub condition in parentheses, add another Condition with the same logical operator separator.
 */
class Condition implements \Countable, \Stringable
	{
	/** @var array<array> $conditions */
	private array $conditions = [];

	/**
	 * Start a Condition with a Field Operator Value (FOV) tupple.
	 *
	 * Will try to parse FOV from string if $operator is null.
	 *
	 * @param ?string    $field    single name (no .) of a field existing the the table.  Will try to parse FOV from string if $operator is null.
	 * @param mixed     $value    to test field against.  Must be string for LIKE operators and an array for IN operators.
	 * @param \PHPFUI\ORM\Operator $operator comparision of your choice
	 */
	public function __construct(?string $field = null, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal())
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
			if (! \is_object($parts[1]))
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
					$escapedField = '`' . \str_replace('.', '`.`', (string)$field) . '`';
					$retVal .= "{$first}{$escapedField}{$operator} ";
					$first = ' ';

					if (\is_array($value) || $value instanceof \PHPFUI\ORM\Table)
						{
						$retVal .= '(' . \implode(',', \array_fill(0, \count($value), '?')) . ')';
						}
					elseif (null !== $value)
						{
						$retVal .= '?';
						}
					}
				elseif (\is_object($value))
					{
					$retVal .= "{$first}{$field}{$operator} {$value}";
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

	public function getJSON() : string
		{
		return \json_encode($this->getConditionArray($this->conditions), JSON_THROW_ON_ERROR);
		}

	/**
	 * Add logical AND between FOV tupples or Condition
	 */
	public function and(string | \PHPFUI\ORM\Condition $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('AND', $condition, $operator, $value);
		}

	/**
	 * Add logical AND NOT between FOV tupples or Condition
	 */
	public function andNot(string | \PHPFUI\ORM\Condition $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('AND NOT', $condition, $operator, $value);
		}

	/**
	 * @return int  the number of FOV tupples in the condition
	 */
	public function count() : int
		{
		return \count($this->conditions);
		}

	/**
	 * @return string[]  of all the fields used by the condition
	 */
	public function getFields(?self $condition = null) : array
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
			if (! \is_object($parts[1]))
				{
				$value = $parts[3];

				if ($value instanceof \PHPFUI\ORM\Table)
					{
					$input = [];
					$sql = $value->getSQL($input);
					$retVal = \array_merge($retVal, \PHPFUI\ORM::getValueArray($sql, $input));
					}
				elseif (\is_array($value))
					{
					$retVal = \array_merge($retVal, $value);
					}
				elseif (\is_object($value))
					{
					// skip
					}
				elseif (null !== $value)
					{
					$retVal[] = $value;
					}
				}
			else
				{
				$retVal = \array_merge($retVal, $parts[1]->getInput());
				}
			}

		return $retVal;
		}

	/**
	 * Add logical OR between FOV tupples or Condition
	 */
	public function or(string | \PHPFUI\ORM\Condition $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('OR', $condition, $operator, $value);
		}

	/**
	 * Add logical OR NOT between FOV tupples or Condition
	 */
	public function orNot(string | \PHPFUI\ORM\Condition $condition, mixed $value = null, \PHPFUI\ORM\Operator $operator = new \PHPFUI\ORM\Operator\Equal()) : self
		{
		return $this->add('OR NOT', $condition, $operator, $value);
		}

	private function getConditionArray(array $conditions) : array
		{
		$data = [];

		foreach ($conditions as $condition)
			{
			if (4 == (\is_countable($condition) ? \count($condition) : 0))
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

	/**
	 * Internal method to type check $condition parameter
	 */
	private function add(string $logical, string | \PHPFUI\ORM\Condition $condition, \PHPFUI\ORM\Operator $operator, mixed $value) : static
		{
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

		if ('string' == \gettype($condition))
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
	}
