<?php

namespace PHPFUI\ORM;

/**
 * Validator is an abstract class for Record validation See [\PHPFUI\ORM\Record\Validation](/System/sayaPhpDocumentation?n=App%5CRecord%5CValidation) namespace for examples.
 *
 * Individual validators are listed in the table below. Validators can be combined.  For example, a field can be **required**, and have a **minlength** and **maxlength**. Validators can have parameters. Parameters are separated by a colon (:) and then commas for each separate parameter.
 *
 * ## Usage
 *
 * ```php
 * $record = new \PHPFUI\ORM\Record\Example($_POST);
 * $validationErrors = $record->validate();
 * ```
 * <br>$validationErrors is an array indexed by field name containing an array of translated errors.
 *
 * | Validator Name  | Description | Parameters |
 * | -------------- | ----------- | ----------- |
 * | alnum          | Numbers and characters only (ctype_alnum) | None |
 * | alpha          | Characters only (ctype_alpha) | None |
 * | bool           | Must be one or zero | None |
 * | card           | Credit card number (LUHN validation) | None |
 * | color          | HTML color (#fff or #fafbfc, '#' is optional) | None |
 * | cvv            | Credit card cvv number | None |
 * | date           | Loosely formatted date (Y-M-D) | None |
 * | dateISO        | Strictly formatted ISO Date (YYYY-MM-DD) | None |
 * | datetime       | Loosely formatted date (Y-M-D) followed by time format | None |
 * | day_month_year | Loosely formatted date (D-M-Y) | None |
 * | domain         | Valid domain | None |
 * | email          | Valid email | None |
 * | enum           | MySQL enum value, case insensitive | comma separated list of identifiers<br>**Example:** enum:GET,POST,PUT,DELETE |
 * | enum_exact     | MySQL enum value, case sensitive | comma separated list of identifiers<br>**Example:** enum:ssl,tls |
 * | integer        | Whole number, no fractional part | None |
 * | maxlength      | Length must be greater or equal | Optional length, else MySQL limit |
 * | maxvalue       | Value must be greater or equal | number, required |
 * | minlength      | Must be less than or equal | number, default field size |
 * | minvalue       | Must be less than or equal | number, required |
 * | month_day_year | Loosely formatted date (M-D-Y) | None |
 * | month_year     | Loosely formatted Month Year | None |
 * | number         | Floating point number or whole number | None |
 * | required       | Field is required, can't be null or blank, 0 is OK | None |
 * | time           | Time (ampm or military), : separators | None |
 * | unique         | Column must be a unique value | See Below |
 * | url            | Valid URL (ftp, http, etc) | None |
 * | website        | Valid URL (http or https only) | None |
 * | year_month     | Loosely formatted Year Month | None |
 *
 * ## Unique Parameters
 * Without any parameters, the **unique** validator will make sure no other record has a matching value for the field being validated. The current record is always exempted from the unique test so it can be updated.
 *
 * If there are parameters, the first parameter must be a field of the current record. If this is the only parameter, or if the next parameter is also a field of the record, then the unique test is only done with the value of this field set to the current record's value.
 *
 * If the next parameter is not a field of the record, it is used as a value to match for the preceeding field for the unique test.
 *
 * The above repeats until all parameters are exhausted.
 *
 * **Examples:**
 *
 * Suppose you have a table with the following fields:
 * * name
 * * company
 * * division
 * * type
 *
 * You want the name to be unique per company: *unique:company*
 * You want the name to be unique per division with in the company: *unique:company,division*
 * You want the name to be unique for a specific type in the division: *unique:type,shoes,division*
 * You want the name to be unique for a specific type and division: *unique:type,shoes,division,10*
 *
 * ## Optional Validation
 * You may need to do additional checks for a specific record type.  A second parameter can be passed to the contructor which would represent the original values of the record.
 *
 * You can also pass an optional method to validate to perform more complex validation. By default, insert, update, and delete are standard methods that are used by the \PHPFUI\ORM\Controller\Record class will use.
 */
abstract class Validator
	{
	/** @var string[] */
	public static array $dateSeparators = ['-', '.', '_', ':', '/'];

	public static array $validators = [];

	private string $currentField = '';

	private bool $currentRequired = false;

	/** @var array<string, array<mixed>> */
	private array $definitions = [];

	/** @var array<string, string[]> */
	private array $errors = [];

	public function __construct(protected \PHPFUI\ORM\Record $record, protected ?\PHPFUI\ORM\Record $originalRecord = null)
		{
		$this->definitions = $this->record->getFields();
		}

	/**
	 * Return any errors.
	 *
	 * @return array<string, string[]>  indexed by field(s) with error and array of translated errors.
	 */
	public function getErrors() : array
		{
		return $this->errors;
		}

	/**
	 * Return true if the entire record validates
	 *
	 * @param  string $optionalMethod will be called if it matches an existing method.  This can be used
	 * to more complex checks that need more involved validations. By default, insert, update, and
	 * delete are standard methods that are used by the \PHPFUI\ORM\Controller\Record class.  The optionalMethod
	 * overrides the normal validation, so if you want the normal validations, the optionalMethod will
	 * need to call the validate function again itself.
	 *
	 * @return bool                   true if valid
	 */
	public function validate(string $optionalMethod = '') : bool
		{
		$this->errors = [];

		if ($optionalMethod && \method_exists($this, $optionalMethod))
			{
			$this->errors = $this->{$optionalMethod}();

			return empty($this->errors);
			}

		foreach ($this->definitions as $field => $definition)
			{
			$this->currentField = $field;
			$errors = $this->getFieldErrors($this->record->{$field}, $definition, static::$validators[$field] ?? []);

			if ($errors)
				{
				$this->errors[$field] = $errors;
				}
			}

		return empty($this->errors);
		}

	/**
	 * Gets the errors for a value with the record definition and associated validators
	 *
	 * @param array<int, array<mixed>> $definition
	 *
	 * @return array  of errors of translated text
	 */
	private function getFieldErrors(mixed $value, array $definition, array $validators) : array
		{
		$errors = $parameters = [];

		if (! \count($validators))
			{
			return $errors;
			}

		// if required, blank value is failure
		$length = \strlen("{$value}");

		$this->currentRequired = false;

		if (\in_array('required', $validators))
			{
			$this->currentRequired = true;

			if (! $length)
				{
				$errors[] = \PHPFUI\ORM::trans('.validator.required');

				return $errors;
				}
			}
		elseif (! $length)
			{
			// if not required, a blank value is a pass

			return $errors;
			}

		foreach ($validators as $validator)
			{
			$parts = \explode(':', (string)$validator);

			if (\count($parts) > 1)
				{
				$parameters = \explode(',', $parts[1]);
				}
			$validator = $parts[0];
			$method = 'validate_' . $validator;

			if (\method_exists($this, $method))
				{
				$error = $this->{$method}($value, $parameters, $definition);

				if ($error)
					{
					$errors[] = $error;
					}
				}
			else
				{
				throw new \Exception("Validator {$validator} (validate_{$validator} method) not found in class " . self::class);
				}
			}

		return $errors;
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_alpha(mixed $value, array $parameters, array $definition) : string
		{
		return \ctype_alpha((string)$value) ? '' : \PHPFUI\ORM::trans('.validator.alpha', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_alpha_numeric(mixed $value, array $parameters, array $definition) : string
		{
		return \ctype_alnum((string)$value) ? '' : \PHPFUI\ORM::trans('.validator.alnum', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_bool(mixed $value, array $parameters, array $definition) : string
		{
		return \ctype_digit((string)$value) && (0 == $value || 1 == $value) ? '' : \PHPFUI\ORM::trans('.validator.bool', ['value' => $value]);
		}

	/**
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_card($number, array $definition) : string
		{
		// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
		$number = \preg_replace('/\D/', '', (string)$number);

		// Set the string length and parity
		$number_length = \strlen($number);
		$parity = $number_length % 2;

		// Loop through each digit and do the maths
		$total = 0;

		for ($i = 0; $i < $number_length; ++$i)
			{
			$digit = (int)$number[$i];
			// Multiply alternate digits by two
			if ($i % 2 == $parity)
				{
				$digit *= 2;
				// If the sum is two digits, add them together (in effect)
				if ($digit > 9)
					{
					$digit -= 9;
					}
				}
			// Total up the digits
			$total += $digit;
			}

		// If the total mod 10 equals 0, the number is valid
		return (0 == $total % 10) ? '' : \PHPFUI\ORM::trans('.validator.card', ['value' => $number]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_color(mixed $value, array $parameters, array $definition) : string
		{
		$len = 0;

		$testValue = '#' == $value[0] ? \substr((string)$value, 1) : $value;

		if (\ctype_xdigit((string)$testValue))
			{
			$len = \strlen((string)$testValue);
			}

		return 3 == $len || 6 == $len ? '' : \PHPFUI\ORM::trans('.validator.color', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_cvv(mixed $value, array $parameters, array $definition) : string
		{
		$int = (int)$value;

		return $int >= 100 && $int <= 9999 ? '' : \PHPFUI\ORM::trans('.validator.cvv', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_date(mixed $value, array $parameters, array $definition) : string
		{
		$year = 0;
		$month = 1;
		$day = 2;
		$parts = \explode('/', \str_replace(self::$dateSeparators, '/', (string)$value));
		// allow zero dates if not required
		if (! $this->currentRequired && ! \array_sum($parts))
			{
			return '';
			}

		return \checkdate((int)($parts[$month] ?? 0), (int)($parts[$day] ?? 0), (int)($parts[$year] ?? 0)) ? '' : \PHPFUI\ORM::trans('.validator.date', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_dateISO(mixed $value, array $parameters, array $definition) : string
		{
		$year = 0;
		$month = 1;
		$day = 2;
		$parts = \explode('-', (string)$value);
		$year = \sprintf('%04d', (int)($parts[$year] ?? 0));
		$month = \sprintf('%02d', (int)($parts[$month] ?? 0));
		$day = \sprintf('%02d', (int)($parts[$day] ?? 0));

		return (4 == \strlen($year) && 2 == \strlen($month) && 2 == \strlen($day) && \checkdate((int)$month, (int)$day, (int)$year)) ? '' : \PHPFUI\ORM::trans('.validator.dateISO', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_datetime(mixed $value, array $parameters, array $definition) : string
		{
		if (\strpos((string)$value, 'T'))
			{
			$parts = \explode('T', (string)$value);
			}
		else
			{
			$parts = \explode(' ', (string)$value);
			}

		$error = $this->validate_date($parts[0], $parameters, $definition);

		if ($error)
			{
			return $error;
			}

		return $this->validate_time($parts[1] ?? '', $parameters, $definition);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_day_month_year(mixed $value, array $parameters, array $definition) : string
		{
		$year = 2;
		$month = 1;
		$day = 0;
		$parts = \explode('/', \str_replace(self::$dateSeparators, '/', (string)$value));
		// allow zero dates if not required
		if (! $this->currentRequired && ! \array_sum($parts))
			{
			return '';
			}

		return \checkdate((int)($parts[$month] ?? 0), (int)($parts[$day] ?? 0), (int)($parts[$year] ?? 0)) ? '' : \PHPFUI\ORM::trans('.validator.day_month_year', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_domain(mixed $value, array $parameters, array $definition) : string
		{
		return false !== \filter_var($value, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME) ? '' : \PHPFUI\ORM::trans('.validator.domain', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_email(mixed $value, array $parameters, array $definition) : string
		{
		return false !== \filter_var($value, \FILTER_VALIDATE_EMAIL) ? '' : \PHPFUI\ORM::trans('.validator.email', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_enum(mixed $value, array $parameters, array $definition) : string
		{
		$valueUC = \strtoupper((string)$value);
		$parametersUC = [];

		foreach ($parameters as $enum)
			{
			$parametersUC[] = \strtoupper((string)$enum);
			}

		return \in_array($valueUC, $parametersUC) ? '' : \PHPFUI\ORM::trans('.validator.enum', ['value' => $value, 'valid' => \implode(',', $parameters)]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_enum_exact(mixed $value, array $parameters, array $definition) : string
		{
		return \in_array($value, $parameters) ? '' : \PHPFUI\ORM::trans('.validator.enum', ['value' => $value, 'valid' => \implode(',', $parameters)]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_integer(mixed $value, array $parameters, array $definition) : string
		{
		return false !== \filter_var($value, \FILTER_VALIDATE_INT) ? '' : \PHPFUI\ORM::trans('.validator.integer', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_maxlength(mixed $value, array $parameters, array $definition) : string
		{
		$length = $parameters[0] ?? $definition[\PHPFUI\ORM\Record::LENGTH_INDEX];

		// @phpstan-ignore-next-line
		return \strlen((string)$value) <= $length ? '' : \PHPFUI\ORM::trans('.validator.maxlength', ['value' => $value, 'length' => $length]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_maxvalue(mixed $value, array $parameters, array $definition) : string
		{
		if (! isset($parameters[0]))
			{
			return '';
			}

		return $parameters[0] >= $value ? '' : \PHPFUI\ORM::trans('.validator.maxvalue', ['value' => $value, 'max' => $parameters[0]]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_minlength(mixed $value, array $parameters, array $definition) : string
		{
		$length = $parameters[0] ?? $definition[\PHPFUI\ORM\Record::LENGTH_INDEX];

		// @phpstan-ignore-next-line
		return \strlen((string)$value) >= $length ? '' : \PHPFUI\ORM::trans('.validator.minlength', ['value' => $value, 'length' => $length]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_minvalue(mixed $value, array $parameters, array $definition) : string
		{
		if (! isset($parameters[0]))
			{
			return '';
			}

		return $parameters[0] <= $value ? '' : \PHPFUI\ORM::trans('.validator.minvalue', ['value' => $value, 'min' => $parameters[0]]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_month_day_year(mixed $value, array $parameters, array $definition) : string
		{
		$year = 2;
		$month = 0;
		$day = 1;
		$parts = \explode('/', \str_replace(self::$dateSeparators, '/', (string)$value));
		// allow zero dates if not required
		if (! $this->currentRequired && ! \array_sum($parts))
			{
			return '';
			}

		return \checkdate((int)($parts[$month] ?? 0), (int)($parts[$day] ?? 0), (int)($parts[$year] ?? 0)) ? '' : \PHPFUI\ORM::trans('.validator.month_day_year', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_month_year(mixed $value, array $parameters, array $definition) : string
		{
		$year = 1;
		$month = 0;
		$day = 1;
		$parts = \explode('/', \str_replace(self::$dateSeparators, '/', (string)$value));

		return \checkdate((int)($parts[$month] ?? 0), $day, (int)($parts[$year] ?? 0)) ? '' : \PHPFUI\ORM::trans('.validator.month_year', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_number(mixed $value, array $parameters, array $definition) : string
		{
		return false !== \filter_var($value, \FILTER_VALIDATE_FLOAT) ? '' : \PHPFUI\ORM::trans('.validator.number', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_required(mixed $value, array $parameters, array $definition) : string
		{
		return \strlen("{$value}") ? '' : \PHPFUI\ORM::trans('.validator.required');
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_time(mixed $value, array $parameters, array $definition) : string
		{
		$hours = ['H', 'h', 'G', 'g', ];
		$tails = [':i:s', ':i', '', ];
		$meridian = ['A', 'a', ''];

		foreach ($hours as $hour)
			{
			foreach ($tails as $tail)
				{
				foreach ($meridian as $ampm)
					{
					$format = $hour . $tail . $ampm;
					$t = \DateTime::createFromFormat($format, $value);

					if ($t && ($t->format($format) === $value))
						{
						return '';
						}
					}
				}
			}

		return \PHPFUI\ORM::trans('.validator.time', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_unique(mixed $value, array $parameters, array $definition) : string
		{
		$class = '\\' . \PHPFUI\ORM::$tableNamespace . '\\' . $this->record->getTableName();
		$table = new $class();
		// look up the record in the table.  Can't be itself.
		$condition = new \PHPFUI\ORM\Condition();
		$primaryKeys = $this->record->getPrimaryKeys();

		if (1 == \count($primaryKeys))
			{
			$primaryKey = \array_key_first($primaryKeys);
			$condition->and($primaryKey, $this->record->{$primaryKey}, new \PHPFUI\ORM\Operator\NotEqual());
			}
		$field = $this->currentField;
		$condition->and($field, $this->record->{$field});

		while (\count($parameters))
			{
			$field = \array_shift($parameters);

			if (isset($this->definitions[$field]))
				{
				$value = $this->record->{$field};

				if (\count($parameters))
					{
					$next = \array_shift($parameters);

					if (isset($this->definitions[$next]))
						{
						\array_unshift($parameters, $next);
						}
					else
						{
						$value = $next;
						}
					}
				$condition->and($field, $value);
				}
			else
				{
				throw new \Exception("{$field} is not a field of {$this->record->getTableName()}");
				}
			}

		$table->setWhere($condition);

		return 0 == (\is_countable($table) ? \count($table) : 0) ? '' : \PHPFUI\ORM::trans('.validator.unique', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_url(mixed $value, array $parameters, array $definition) : string
		{
		return false !== \filter_var($value, \FILTER_VALIDATE_URL) ? '' : \PHPFUI\ORM::trans('.validator.url', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_website(mixed $value, array $parameters, array $definition) : string
		{
		$parts = \explode('://', \strtolower((string)$value));
		$error = 2 != \count($parts) || ! \in_array($parts[0], ['http', 'https']);

		return (! $error && false !== \filter_var($value, \FILTER_VALIDATE_URL)) ? '' : \PHPFUI\ORM::trans('.validator.website', ['value' => $value]);
		}

	/**
	 * @param string[] $parameters
	 * @param array<int, array<mixed>> $definition
	 */
	private function validate_year_month(mixed $value, array $parameters, array $definition) : string
		{
		$year = 0;
		$month = 1;
		$day = 1;
		$parts = \explode('/', \str_replace(self::$dateSeparators, '/', (string)$value));

		return \checkdate((int)($parts[$month] ?? 0), $day, (int)($parts[$year] ?? 0)) ? '' : \PHPFUI\ORM::trans('.validator.year_month', ['value' => $value]);
		}
	}
