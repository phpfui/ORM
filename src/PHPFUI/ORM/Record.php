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
	protected static bool $autoIncrement;

	protected static bool $deleteChildren = true;

	protected bool $empty = true;

	/** @var array <string,\PHPFUI\ORM\FieldDefinition> */
	protected static array $fields;

	protected bool $loaded = false;

	/** @var array<string> */
	protected static array $primaryKeys;

	protected static string $table;

	protected string $validator = '';

	/** @var array<string,array<string>> */
	protected static array $virtualFields = [];

	/** @var array<string> */
	private static array $sqlDefaults = [
		'CURRENT_TIMESTAMP',
		'CURRENT_DATE',
		'true',
		'false',
		"b'0'",
		"b'1'",
	];

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
		$this->initFieldDefinitions();
		$this->setEmpty();
		$type = $parameter instanceof \PHPFUI\ORM\DataObject ? \PHPFUI\ORM\DataObject::class : \get_debug_type($parameter);

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

				if (1 == \count(static::$primaryKeys) && 'int' == static::$fields[static::$primaryKeys[0]]->phpType)
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
				$this->setFrom($parameter->current);

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
			return $this->current[$field] ?? null;
			}

		// could be a related record, see if has a matching Id
		if (\array_key_exists($field . \PHPFUI\ORM::$idSuffix, static::$fields))
			{
			$type = '\\' . \PHPFUI\ORM::$recordNamespace . '\\' . \PHPFUI\ORM::getBaseClassName($field);

			if (\class_exists($type))
				{
				return new $type($this->current[$field . \PHPFUI\ORM::$idSuffix] ?? null);
				}
			}

		return parent::__get($field);
		}

	/**
	 * @inherit
	 */
	public function __isset(string $field) : bool
		{
		return (bool)(parent::__isset($field) || \array_key_exists($field, static::$virtualFields));
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

			if ($field == $haveType)
				{
				if ($value->empty())
					{
					$this->current[$id] = static::$fields[$id]->nullable ? null : 0;

					return;
					}
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
		$expectedType = static::$fields[$field]->phpType;
		$haveType = \get_debug_type($value);

		if (null === $value)
			{
			if (! static::$fields[$field]->nullable)
				{
				$message = static::class . "::{$field} does not allow nulls";
				\PHPFUI\ORM::log(\Psr\Log\LogLevel::WARNING, $message);

				throw new \PHPFUI\ORM\Exception($message);
				}
			}
		elseif ($haveType != $expectedType)
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

	public function blankDate(?string $date) : string
		{
		if ('1000-01-01' > $date)
			{
			return '';
			}

		return $date;
		}

	/**
	 * clean is called before insert or update. Override to implement cleaning on a specific record
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
	 * @return array<string,\PHPFUI\ORM\FieldDefinition> of FieldDefinition indexed by field name
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

		return static::$fields[$field]->length;
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
		$pdo = \PHPFUI\ORM::pdo();

		if (! $pdo->sqlite && ! $pdo->postGre)
			{
			return $this->privateInsert(false, 'ignore ');
			}

		$id = $this->privateInsert(false);

		if (! $id)
			{
			\PHPFUI\ORM::getInstance()->clearErrors();
			}

		return $id;
		}

	/**
	 * Inserts current data into table or updates if duplicate key
	 *
	 * @return int | bool inserted id if auto increment, true on insertion if not auto increment or false on error
	 */
	public function insertOrUpdate() : int | bool
		{
		$pdo = \PHPFUI\ORM::pdo();

		if (! $pdo->sqlite)
			{
			return $this->privateInsert(true);
			}

		$id = $this->privateInsert(false);

		if (false === $id)
			{
			\PHPFUI\ORM::getInstance()->clearErrors();

			$id = $this->update();
			$keys = $this->getPrimaryKeyValues();

			if (1 == \count($keys))
				{
				$id = \array_shift($keys);
				}
			}

		return $id;
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

		$this->correctTypes();

		return true;
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
		return $this->read($this->getPrimaryKeyValues());
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
			if (null === $description->defaultValue)  // no default value
				{
				$this->current[$field] = null; // can't be null, so we can set to null, user must set
				}
			else	// has default value, if SQL default, set to null, otherwise default value
				{
				$this->current[$field] = \in_array($description->defaultValue, self::$sqlDefaults) ? null : $description->defaultValue;
				}
			}

		$this->correctTypes();

		return $this;
		}

	/**
	 * Sets the object to values in the array.  Invalid array values are ignored.
	 *
	 * @param array<string,mixed> $values
	 * @param array<string> $allowedFields list of allowed field names, other fields names will be ignored. Empty array updates all valid fields.
	 * @param bool $loaded set to true if you want to simulated being loaded from the db.
	 */
	public function setFrom(array $values, array $allowedFields = [], bool $loaded = false) : static
		{
		$this->loaded = $loaded;

		if (\count($allowedFields))
			{
			$values = \array_intersect_key($values, \array_flip($allowedFields));
			}

		foreach ($values as $field => $value)
			{
			if (isset(static::$fields[$field]))
				{
				$this->empty = false;
				$this->current[$field] = $value;
				}
			}

		$this->correctTypes();

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
					if (empty($value) && \in_array(static::$fields[$field]->sqlType, $dateTimes))
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

	protected function correctTypes() : static
		{
		// cast to correct values as ints, floats, etc are read in from PDO as strings
		foreach (static::$fields as $field => $row)
			{
			$relationship = static::$virtualFields[$field] ?? false;

			if (\is_array($relationship))
				{
				$relationshipClass = \array_shift($relationship);
				$relationshipObject = new $relationshipClass($this, $field);
				$relationshipObject->setValue($relationshipObject->fromPHPValue($this->current[$field] ?? null, $relationship), $relationship);
				}
			elseif (\array_key_exists($field, $this->current))
				{
				// don't touch nulls if allowed by the field or the primary key
				if (null === $this->current[$field])
					{
					if ($row->nullable || \in_array($field, static::$primaryKeys))
						{
						continue;
						}
					}

				switch ($row->phpType)
					{
					case 'int':
						$this->current[$field] = (int)$this->current[$field];

						break;

					case 'float':
						$this->current[$field] = (float)$this->current[$field];

						break;

					case 'bool':
						$this->current[$field] = (bool)$this->current[$field];

						break;
					}
				}
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

	protected function validateFieldExists(string $field) : void
		{
		if (! isset(static::$fields[$field]))
			{
			$message = static::class . "::{$field} is not a valid field";
			\PHPFUI\ORM::log(\Psr\Log\LogLevel::ERROR, $message);

			throw new \PHPFUI\ORM\Exception($message);
			}
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
		$primaryKey = static::$primaryKeys[0] ?? '';

		foreach ($this->current as $key => $value)
			{
			if (isset(static::$fields[$key]))
				{
				$definition = static::$fields[$key];

				if (null === $value && null !== $definition->defaultValue)
					{
					continue;
					}
				// && \in_array($definition->defaultValue, self::$sqlDefaults))

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
			if (\PHPFUI\ORM::pdo()->postGre)
				{
				$updateSql = " ON CONFLICT ({$primaryKey}) DO UPDATE SET ";
				}
			else
				{
				$updateSql = ' on duplicate key update ';
				}
			$comma = '';
			$inputCount = \count($input);

			foreach ($this->current as $key => $value)
				{
				if (isset(static::$fields[$key]))
					{
					$definition = static::$fields[$key];

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

		if ($returnValue)
			{
			if (static::$autoIncrement)
				{
				$returnValue = (int)\PHPFUI\ORM::lastInsertId(static::$primaryKeys[0], $table);

				if ($returnValue)
					{
					$this->current[static::$primaryKeys[0]] = $returnValue;
					}
				}

			$this->loaded = true;	// record is effectively read from the database now
			}

		return $returnValue;
		}
	}
