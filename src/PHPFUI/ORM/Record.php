<?php

namespace PHPFUI\ORM;

/**
 * Basic CRUD class implementing insert (create), read, update and delete methods.
 *
 * Classes that derive from **\PHPFUI\ORM\Record** must define the static members
 *
 * There is only one record associated with a single CRUD object.
 *
 * Members of the class can be accessed by their database table field name, case sensitive.  On setting a member, it will be cast to the correct PHP type for the field.
 */
abstract class Record extends DataObject
	{
	public const ALLOWS_NULL_INDEX = 3;

	public const DEFAULT_INDEX = 4;

	public const LENGTH_INDEX = 2;

	public const MYSQL_TYPE_INDEX = 0;

	public const PHP_TYPE_INDEX = 1;

	protected static bool $autoIncrement = false;

	protected static bool $deleteChildren = true;

	/** @var array<string,array<callable>> */
	protected static array $displayTransforms = [];

	protected bool $empty = true;

	/** @var array<string,array<mixed>> */
	protected static array $fields = [];

	protected bool $loaded = false;

	/** @var array<string> */
	protected static array $primaryKeys = [];

	/** @var array<string,array<callable>> */
	protected static array $setTransforms = [];

	protected static string $table = '';

	protected string $validator = '';

	/** @var array<string,array<string>> */
	protected static array $virtualFields = [];

	/**
	 * Construct a CRUD object
	 *
	 * Reads from the database based on the parameters passed to the constructor.  No parameters creates an empty object.
	 *
	 * ##### Possible $parameter types and values
	 * - **int** primary key value, will load object values if the primary key value exists
	 * - **string** primary key value, will load object values if the primary key value exists
	 * - **array** record is attempted to be read from database using the values of the fields provided.
	 * - **\PHPFUI\ORM\DataObject** record is constructed from an existing DataObject
	 * - **null** (default) constructs an empty object
	 *
	 * @param  int|array<string,mixed>|null|string $parameter
	 */
	public function __construct(int|array|null|string|\PHPFUI\ORM\DataObject $parameter = null)
		{
		$this->setEmpty();
		$type = \get_debug_type($parameter);

		switch ($type)
		  {
			case 'string':

				if (1 == \count(static::$primaryKeys))
					{
					$this->read($parameter);
					}
				else
					{
					throw new \PHPFUI\ORM\Exception(static::class . ' has no string primary key');
					}

				break;

			case 'int':

				if (1 == \count(static::$primaryKeys) && 'int' == static::$fields[static::$primaryKeys[0]][self::PHP_TYPE_INDEX])
					{
					$this->read($parameter);
					}
				else
					{
					throw new \PHPFUI\ORM\Exception(static::class . ' does not have an integer primary key');
					}

				break;

			case 'array':

				$this->read($parameter);

				break;

			case \PHPFUI\ORM\DataObject::class:
				$this->current = \array_intersect_key($parameter->current, static::$fields);

				break;

			}
		}

	/**
	 * Allows for $object->field syntax
	 *
	 * Unset fields will return null
	 */
	public function __get(string $field) : mixed
		{
		$relationship = static::$virtualFields[$field] ?? false;

		if (\is_array($relationship))
			{
			$relationshipClass = \array_shift($relationship);
			$relationshipObject = new $relationshipClass($this, $field);

			return $relationshipObject->getValue($relationship);
			}

		if (isset(static::$fields[$field]))
			{
			return $this->displayTransform($field);
			}

		$id = $field . \PHPFUI\ORM::$idSuffix;

		if (\array_key_exists($id, $this->current))
			{
			$type = '\\' . \PHPFUI\ORM::$recordNamespace . '\\' . \PHPFUI\ORM::getBaseClassName($field);

			if (\class_exists($type))
				{
				return new $type($this->current[$id]);
				}
			}

		throw new \PHPFUI\ORM\Exception(static::class . "::{$field} is not a valid field");
		}

	/**
	 * Allows for empty($object->field) to work correctly
	 */
	public function __isset(string $field) : bool
		{
		return (bool)(\array_key_exists($field, $this->current) || \array_key_exists($field, static::$virtualFields) || \array_key_exists($field . \PHPFUI\ORM::$idSuffix, $this->current));
		}

	public function __set(string $field, mixed $value) : void
		{
		$relationship = static::$virtualFields[$field] ?? false;

		if (\is_array($relationship))
			{
			$relationshipClass = \array_shift($relationship);
			$relationshipObject = new $relationshipClass($this, $field);
			$relationshipObject->setValue($value, $relationship);

			return;
			}

		$id = $field . \PHPFUI\ORM::$idSuffix;

		if (isset(static::$fields[$id]) && $value instanceof \PHPFUI\ORM\Record)
			{
			$haveType = $value->getTableName();

			if ($value instanceof \PHPFUI\ORM\Record && $field == $haveType)
				{
				$this->empty = false;

				if (empty($value->{$id}))
					{
					$this->current[$id] = $value->insert();
					}
				else
					{
					$this->current[$id] = $value->{$id};
					}

				return;
				}

			$haveType = \PHPFUI\ORM::getBaseClassName($haveType);
			$recordNamespace = \PHPFUI\ORM::$recordNamespace;
			$message = static::class . "::{$field} is of type \\{$recordNamespace}\\" . \PHPFUI\ORM::getBaseClassName($field) . " but being assigned a type of \\{$recordNamespace}\\{$haveType}}";
			\PHPFUI\ORM::log(\Psr\Log\LogLevel::ERROR, $message);

			throw new \PHPFUI\ORM\Exception($message);
			}

		$this->validateFieldExists($field);

		if (isset(static::$setTransforms[$field]))
			{
			$value = static::$setTransforms[$field]($value);
			}

		$expectedType = static::$fields[$field][self::PHP_TYPE_INDEX];
		$haveType = \get_debug_type($value);

		if (null !== $value && $haveType != $expectedType)
			{
			$message = static::class . "::{$field} is of type {$expectedType} but being assigned a type of {$haveType}";
			\PHPFUI\ORM::log(\Psr\Log\LogLevel::WARNING, $message);

			// do the conversion
			switch ($expectedType)
				{
				case 'string':
					$value = (string)$value;

					break;

				case 'int':
					$value = (int)$value;

					break;

				case 'float':
					$value = (float)$value;

					break;

				case 'bool':
					$value = (bool)$value;

					break;
				}
			}
		$this->empty = false;
		$this->current[$field] = $value;
		}

	/**
	 * Add a transform for get.  Callback is passed value.
	 */
	public static function addDisplayTransform(string $field, callable $callback) : void
		{
		static::$displayTransforms[$field] = $callback;
		}

	/**
	 * Add a transform for set.  Callback is passed value.
	 */
	public function addSetTransform(string $field, callable $callback) : static
		{
		static::$setTransforms[$field] = $callback;

		return $this;
		}

	public function blankDate(?string $date) : string
		{
		if ('1000-01-01' > $date)
			{
			return '';
			}

		return $date;
		}

	/**
	 * clean is called before insert or update. Override to impliment cleaning on a specific record
	 */
	public function clean() : static
		{
		return $this;
		}

	/**
	 * Alias of insert
	 */
	public function create() : int
		{
		return $this->insert();
		}

	/**
	 * Deletes the record (and children) currently pointed to by the data
	 *
	 * @return bool  true if record deleted
	 */
	public function delete() : bool
		{
		if (static::$deleteChildren)
			{
			foreach (static::$virtualFields as $field => $relationship)
				{
				$relationshipClass = \array_shift($relationship);

				if (\PHPFUI\ORM\Children::class == $relationshipClass)
					{
					$relationshipObject = new \PHPFUI\ORM\Children($this, $field);
					$relationshipObject->delete($relationship);
					}
				}
			}

		$input = [];
		$table = static::$table;
		$where = $this->buildWhere($this->current, $input);

		if (empty($input) || empty($where))
			{
			return false;
			}

		$sql = "delete from `{$table}` " . $where;

		return \PHPFUI\ORM::execute($sql, $input);
		}

	/**
	 * Transform a field for display
	 */
	public function displayTransform(string $field, mixed $value = null) : mixed
		{
		if (null === $value)
			{
			$value = $this->current[$field] ?? null;
			}

		if (! isset(static::$displayTransforms[$field]))
			{
			return $value;
			}

		return static::$displayTransforms[$field]($value);
		}

	/**
	 * @return bool  true if empty (default values)
	 */
	public function empty() : bool
		{
		return $this->empty;
		}

	/**
	 * @return bool  true if table has an auto increment primary key
	 */
	public function getAutoIncrement() : bool
		{
		return static::$autoIncrement;
		}

	/**
	 * @return array<string,array<mixed>> of fields properties indexed by field name
	 */
	public static function getFields() : array
		{
		return static::$fields;
		}

	/**
	 * @return int Maximium valid field length
	 */
	public function getLength(string $field) : int
		{
		$this->validateFieldExists($field);

		return static::$fields[$field][self::LENGTH_INDEX];
		}

	/**
	 * @return array<string>  primary keys
	 */
	public static function getPrimaryKeys() : array
		{
		return static::$primaryKeys;
		}

	/**
	 * @return array<string, string>  indexed by primary keys containing the key value
	 */
	public function getPrimaryKeyValues() : array
		{
		$retVal = [];

		foreach (static::$primaryKeys as $key)
			{
			$retVal[$key] = $this->current[$key] ?? null;
			}

		return $retVal;
		}

	/**
	 * @return string  table name, case sensitive
	 */
	public static function getTableName() : string
		{
		return static::$table;
		}

	/**
	 * Get the virtual field names
	 *
	 * @return string[]
	 */
	public static function getVirtualFields() : array
		{
		return \array_keys(static::$virtualFields);
		}

	/**
	 * Inserts current data into table
	 *
	 * @return int | bool inserted id if auto increment, true on insertion if not auto increment or false on error
	 */
	public function insert() : int | bool
		{
		return $this->privateInsert(false);
		}

	/**
	 * Inserts current data into table or ignores duplicate key if found
	 *
	 * @return int | bool inserted id if auto increment, true on insertion if not auto increment or false on error
	 */
	public function insertOrIgnore() : int | bool
		{
		return $this->privateInsert(false, 'ignore ');
		}

	/**
	 * Inserts current data into table or updates if duplicate key
	 *
	 * @return int | bool inserted id if auto increment, true on insertion if not auto increment or false on error
	 */
	public function insertOrUpdate() : int | bool
		{
		return $this->privateInsert(true);
		}

	/**
	 * @return bool  true if loaded from the disk
	 */
	public function loaded() : bool
		{
		return $this->loaded;
		}

	/**
	 * Load first from SQL query
	 *
	 * @param array<mixed> $input
	 */
	public function loadFromSQL(string $sql, array $input = []) : bool
		{
		$this->current = \PHPFUI\ORM::getRow($sql, $input);

		if (! $this->current)
			{
			$this->setEmpty();

			return false;
			}
		$this->empty = false;
		$this->loaded = true;

		// cast to correct values as ints, floats, etc are read in from PDO as strings
		foreach (static::$fields as $field => $row)
			{
			switch ($row[1])
				{
				case 'int':
					if (\array_key_exists($field, $this->current))
						{
						$this->current[$field] = (int)$this->current[$field];
						}

					break;

				case 'float':
					if (\array_key_exists($field, $this->current))
						{
						$this->current[$field] = (float)$this->current[$field];
						}

					break;

				case 'bool':
					if (\array_key_exists($field, $this->current))
						{
						$this->current[$field] = (bool)$this->current[$field];
						}

					break;
				}
			}

		return true;
		}

	public function offsetGet(mixed $offset) : mixed
		{
		$this->validateFieldExists($offset);

		return $this->current[$offset] ?? null;
		}

 /**
  * Read a record from the db. If more than one match, only the first is loaded.
  *
  * @param array<string,mixed>|int|string $fields if int|string, primary key, otherwise a key => value array to match on. Multiple field value pairs are anded into the where clause.
  *
  * @return bool  true if a record found
  */
	public function read(array|int|string $fields) : bool
		{
		$input = [];
		$table = static::$table;
		$sql = "select * from `{$table}` " . $this->buildWhere($fields, $input);

		return $this->loadFromSQL($sql, $input);
		}

	/**
	 * Reload the object from the database.  Unsaved fields are discarded.
	 */
	public function reload() : bool
		{
		$keys = [];

		foreach (static::$primaryKeys as $key)
			{
			if (\array_key_exists($key, $this->current))
				{
				$keys[$key] = $this->current[$key];
				}
			}

		return $this->read($keys);
		}

	/**
	 * Save the record, will either update if it exists or insert if not
	 */
	public function save() : int | bool
		{
		return $this->privateInsert(true);
		}

	/**
	 * Set a custom validator class
	 */
	public function setCustomValidator(string $className) : static
		{
		$this->validator = $className;

		return $this;
		}

	/**
	 * Sets all fields to default values
	 */
	public function setEmpty() : static
		{
		$this->empty = true;
		$this->loaded = false;
		$this->current = [];

		foreach (static::$fields as $field => $description)
			{
			$this->current[$field] = $description[self::DEFAULT_INDEX] ?? null;
			}

		return $this;
		}

	/**
	 * Sets the object to values in the array.  Invalid array values are ignored.
	 *
	 * @param array<string,mixed> $values
	 *
	 * @param bool $loaded set to true if you want to simulated being loaded from the db.
	 */
	public function setFrom(array $values, bool $loaded = false) : static
		{
		$this->loaded = $loaded;

		foreach ($values as $field => $value)
			{
			if (isset(static::$fields[$field]))
				{
				$this->empty = false;

				if (isset(static::$setTransforms[$field]))
					{
					$value = static::$setTransforms[$field]($value);
					}
				$this->current[$field] = $value;
				}
			}

		return $this;
		}

	/**
	 * Update the database with the current record based on table primary key
	 */
	public function update() : bool
		{
		$this->clean();
		$table = static::$table;

		$sql = "update `{$table}` set ";
		$input = [];
		$keys = [];
		$comma = '';
		$dateTimes = ['timestamp', 'date', 'time', 'datetime'];

		foreach ($this->current as $field => $value)
			{
			if (isset(static::$fields[$field]))
				{
				if (! \in_array($field, static::$primaryKeys))
					{
					if (empty($value) && \in_array(static::$fields[$field][self::MYSQL_TYPE_INDEX], $dateTimes))
						{
						$value = null;
						}
					$input[] = $value;
					$sql .= $comma . '`' . $field . '`=?';
					$comma = ',';
					}
				else
					{
					$keys[$field] = $value;
					}
				}
			}

		if (empty($comma))
			{
			return false;
			}

		$where = $this->buildWhere($keys, $input);

		if (empty($where))
			{
			return false;
			}

		return \PHPFUI\ORM::execute($sql . $where, $input);
		}

	/**
	 * @return array<string,array<string>> validation errors indexed by offending field containing an array of translated errors
	 */
	public function validate(string $optionalMethod = '', ?self $originalRecord = null) : array
		{
		$parts = \explode('\\', static::class);
		$baseName = \array_pop($parts);
		$class = $this->validator ?: 'App\\Record\\Validation\\' . $baseName;

		if (! \class_exists($class))
			{
			return [];
			}

		$validator = new $class($this, $originalRecord);
		$validator->validate($optionalMethod);

		return $validator->getErrors();
		}

	/**
	 * Lowercases and strips invalid email characters.  Does not validate email address.
	 */
	protected function cleanEmail(string $field) : static
		{
		if (isset($this->current[$field]))
			{
			$this->current[$field] = \preg_replace('/[^a-z0-9\._\-@!#\$%&\'\*\+=\?\^`\{\|\}~]/', '', \strtolower($this->current[$field]));
			}

		return $this;
		}

	/**
	 * removes all non-digits (0-9, . and -)
	 */
	protected function cleanFloat(string $field, int $decimalPoints = 2) : static
		{
		if (isset($this->current[$field]))
			{
			$this->current[$field] = \number_format((float)$this->current[$field], $decimalPoints);
			}

		return $this;
		}

	/**
	 * Converts the field to all lower case
	 */
	protected function cleanLowerCase(string $field) : static
		{
		if (isset($this->current[$field]))
			{
			$this->current[$field] = \strtolower($this->current[$field]);
			}

		return $this;
		}

	/**
	 * removes all non-digits (0-9 and -) from string representation of a number
	 */
	protected function cleanNumber(string $field) : static
		{
		if (isset($this->current[$field]))
			{
			$temp = (int)$this->current[$field];
			$this->current[$field] = "{$temp}";
			}

		return $this;
		}

	/**
	 * removes all invalid characters. (0-9) and regex separators are valid.
	 */
	protected function cleanPhone(string $field, string $regExSeparators = '\\-\\. ') : static
		{
		if (isset($this->current[$field]))
			{
			$this->current[$field] = \preg_replace("/[^0-9{$regExSeparators}]/", '', \strtolower($this->current[$field]));
			}

		return $this;
		}

	/**
	 * Properly capitalizes proper names if in single case. Mixed case strings are not altered.
	 */
	protected function cleanProperName(string $field) : static
		{
		if (isset($this->current[$field]))
			{
			$text = $this->current[$field];
			$lower = \strtolower($text);
			$upper = \strtoupper($text);

			if ($lower != $text && $upper != $text)
				{
				return $this;
				}
			$this->current[$field] = \ucwords($lower);
			}

		return $this;
		}

	/**
	 * Converts the field to all upper case
	 */
	protected function cleanUpperCase(string $field) : static
		{
		if (isset($this->current[$field]))
			{
			$this->current[$field] = \strtoupper($this->current[$field]);
			}

		return $this;
		}

	protected function timeStamp(?int $timeStamp) : string
		{
		if (empty($timeStamp))
			{
			return '';
			}

		return \date('Y-m-d g:i a', $timeStamp);
		}

	/**
	 * Build a where clause
	 *
	 * @param int|array<string,mixed>|string $key if int|string, primary key, otherwise a key => value array of fields to match
	 * @param array<mixed> &$input
	 *
	 * @return string  starting with " where"
	 */
	private function buildWhere(array|int|string $key, array &$input) : string
		{
		if ('*' === $key)
			{
			return '';
			}

		if (! \is_array($key))
			{
			$key = [static::$primaryKeys[0] => $key];
			}
		else
			{ // if all primary keys are set, then use primary keys only

			$keys = [];
			$all = true;

			foreach (static::$primaryKeys as $keyField)
				{
				if (! isset($key[$keyField]))
					{
					$all = false;

					break;
					}
				$keys[$keyField] = $key[$keyField];
				}

			if ($all && \count($keys))
				{
				$key = $keys;
				}
			}

		$and = ' ';
		$sql = '';

		foreach ($key as $field => $value)
			{
			if (isset(static::$fields[$field]))
				{
				$sql .= empty($sql) ? ' where' : '';
				$sql .= $and . '`' . $field . '`=?';
				$input[] = $value;
				$and = ' and ';
				}
			}

		return $sql;
		}

	/**
	 * Inserts current data into table
	 *
	 * @return int | bool inserted id if auto increment, true on insertion if not auto increment or false on error
	 */
	private function privateInsert(bool $updateOnDuplicate, string $ignore = '') : int | bool
		{
		$this->clean();
		$table = static::$table;

		$sql = "insert {$ignore}into `{$table}` (";
		$values = [];
		$whereInput = $input = [];
		$comma = '';

		foreach ($this->current as $key => $value)
			{
			if (isset(static::$fields[$key]))
				{
				$definition = static::$fields[$key];

				if (\array_key_exists(self::DEFAULT_INDEX, $definition) && $value === $definition[self::DEFAULT_INDEX])
					{
					continue;
					}

				if (! static::$autoIncrement || ! (\in_array($key, static::$primaryKeys) && empty($value)))
					{
					$sql .= $comma . '`' . $key . '`';
					$input[] = $value;
					$values[] = '?';
					$comma = ',';
					}
				}
			}

		$sql .= ') values (' . \implode(',', $values) . ')';

		if ($updateOnDuplicate)
			{
			$updateSql = ' on duplicate key update ';
			$comma = '';
			$inputCount = \count($input);

			foreach ($this->current as $key => $value)
				{
				if (isset(static::$fields[$key]))
					{
					$definition = static::$fields[$key];

					if (\array_key_exists(self::DEFAULT_INDEX, $definition) && $value === $definition[self::DEFAULT_INDEX])
						{
						continue;
						}

					if (! \in_array($key, static::$primaryKeys))
						{
						$updateSql .= $comma . '`' . $key . '` = ?';
						$input[] = $value;
						$comma = ',';
						}
					}
				}

			if (\count($input) == $inputCount) // nothing to update but primary keys, ignore input
				{
				$sql = \str_replace('insert into', 'insert ignore into', $sql);
				}
			else
				{
				$sql .= $updateSql;
				}
			}

		$returnValue = \PHPFUI\ORM::execute($sql, $input);

		if (static::$autoIncrement && $returnValue)
			{
			$returnValue = (int)\PHPFUI\ORM::lastInsertId(static::$primaryKeys[0]);

			if ($returnValue)
				{
				$this->current[static::$primaryKeys[0]] = $returnValue;
				}
			}

		$this->loaded = true;	// record is effectively read from the database now

		return $returnValue;
		}

	private function validateFieldExists(string $field) : void
		{
		if (! isset(static::$fields[$field]))
			{
			$message = static::class . "::{$field} is not a valid field";
			\PHPFUI\ORM::log(\Psr\Log\LogLevel::ERROR, $message);

			throw new \PHPFUI\ORM\Exception($message);
			}
		}
	}
