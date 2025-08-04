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
 * $record = new \App\Record\Example();
 * $record->setFrom($_POST);
 * $validationErrors = $record->validate();
 * if (! validationErrors)
 *   {
 *   $insertedId = $record->insert();
 *   }
 * ```
 * **$validationErrors** is an array indexed by field name containing an array of translated errors.
 * ```php
 * foreach ($validationErrors as $field => $fieldErrors)
 *   {
 *   echo "Field {$field} has the following errors:\n";
 *   foreach ($fieldErrors as $error)
 *     {
 *     echo $error . "\n";
 *     }
 *   }
 * ```
 *
 * | Validator Name | Description | Parameters  |
 * | -------------- | ----------- | ----------- |
 * | alpha_numeric  | Numbers and characters only (ctype_alnum) | None |
 * | alpha          | Characters only (ctype_alpha) | None |
 * | bool           | Must be one or zero | None |
 * | card           | Credit card number (LUHN validation) | None |
 * | color          | HTML color (#fff or #fafbfc, '#' is optional) | None |
 * | contains       | Field must contain (case sensitive) | comma separated list of strings |
 * | cvv            | Credit card cvv number | None |
 * | date           | Loosely formatted date (Y-M-D) | None |
 * | dateISO        | Strictly formatted ISO Date (YYYY-MM-DD) | None |
 * | datetime       | Loosely formatted date (Y-M-D) followed by time format | None |
 * | day_month_year | Loosely formatted date (D-M-Y) | None |
 * | domain         | Valid domain | None |
 * | email          | Valid email | None |
 * | ends_with      | Field must end with (case sensitive) | comma separated list of strings |
 * | enum           | MySQL enum value, case insensitive | comma separated list of identifiers. **Example:** enum:Get,Post,Put,Delete |
 * | enum_exact     | MySQL enum value, case sensitive | comma separated list of identifiers. **Example:** enum:ssl,tls |
 * | eq_field       | Equal to field | field, required |
 * | equal          | Value must be equal | value, required |
 * | gt_field       | Greater Than field | field, required |
 * | gte_field      | Greater Than or Equal to field | field, required |
 * | icontains      | Field must contain (case insensitive) | comma separated list of strings |
 * | iends_with     | Field must end with (case insensitive) | comma separated list of strings |
 * | integer        | Whole number, no fractional part | None |
 * | istarts_with   | Field must start with (case insensitive) | comma separated list of strings |
 * | lt_field       | Less Than field | field, required |
 * | lte_field      | Less Than or Equal to field | field, required |
 * | maxlength      | Length must be less than or equal | Optional length, else MySQL limit |
 * | maxvalue       | Value must be less than or equal | value, required |
 * | minlength      | Must be greater than or equal | number, default field size |
 * | minvalue       | Must be greater than or equal | value, required |
 * | month_day_year | Loosely formatted date (M-D-Y) | None |
 * | month_year     | Loosely formatted Month Year | None |
 * | neq_field      | Not Equal to field | field, required |
 * | not_equal      | Value must not be equal | value, required |
 * | number         | Floating point number or whole number | None |
 * | required       | Field is required, can't be null or blank, 0 is OK | None |
 * | starts_with    | Field must start with (case sensitive) | comma separated list of strings |
 * | time           | Time (ampm or military), : separators | None |
 * | unique         | Column must be a unique value | See Below |
 * | url            | Valid URL (ftp, http, etc) | None |
 * | website        | Valid URL (http or https only) | None |
 * | year_month     | Loosely formatted Year Month | None |
 *
 * ## Field Comparison Validators
 * You can compare one field to another on the same **\App\Record** with the field validators.
 * * gt_field
 * * lt_field
 * * gte_field
 * * lte_field
 * * eq_field
 * * neq_field
 *
 * **Example:**
 * ```php
 * class Event extends \PHPFUI\ORM\Validator
 *   {
 *   public static array $validators = [
 *     'endTime' => ['maxlength', 'gt_field:startTime'],
 *     'lastRegistrationDate' => ['required', 'date', 'lte_field:eventDate'],
 *     'newMemberDiscount' => ['required', 'number', 'lte_field:price'],
 *     'publicDate' => ['date', 'lte_field:registrationStartDate', 'lte_field:eventDate'],
 *     'registrationStartDate' => ['date', 'lte_field:lastRegistrationDate', 'lte_field:eventDate'],
 *     'startTime' => ['maxlength', 'lt_field:endTime'],
 *   ];
 *   }
 * ```
 *
 * Field validators take another field name as a parameter and perform the specified condition test. To compare against a specific value, use minvalue, maxvalue, equal or not_equal.
 *
 * ## Unique Parameters
 * Without any parameters, the **unique** validator will make sure no other record has a matching value for the field being validated. The current record is always exempted from the unique test so it can be updated.
 *
 * If there are parameters, the first parameter must be a field of the current record. If this is the only parameter, or if the next parameter is also a field of the record, then the unique test is only done with the value of this field set to the current record's value.
 *
 * If the next parameter is not a field of the record, it is used as a value to match for the preceding field for the unique test.
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
 * ## NOT Operator
 * You can reverse any validator by preceding the validator with an ! (exclamation mark).
 *
 * **Example:**
 * !starts_with:/ will fail if the field starts with a / character
 *
 * ## OR Operator
 * You can validate a field if any one of the validators passes.  Use the vertical bar (|) to separate validators. If one of the validators passes, then the the field is valid.
 *
 * **Example:**
 * website|starts_with:/ will validate a fully qualified http url, or a root relative url.
 *
 * ## Optional Validation
 * You may need to do additional checks for a specific record type.  A second parameter can be passed to the contructor which would represent the original values of the record.
 *
 * You can also pass an optional method to validate to perform more complex validation. If you use an optional method, the validator will not perform the standard validations unless you specifically call the validate() method again without the optional method parameter.
 *
 * ## Multi Validator Example
 * ```php
 * class Order extends \PHPFUI\ORM\Validator
 *   {
 *   public static array $validators = [
 *     'order_date' => ['required', 'maxlength', 'datetime', 'minvalue:2000-01-01', 'maxvalue:2099-12-31'],
 *     ];
 *   }
 * ```
 */
abstract class Validator
	{
	/** @var array<string> */
	public static array $dateSeparators = ['-', '.', '_', ':', '/'];

	/** @var array<string,array<string>> */
	public static array $validators = [];

	protected string $currentField = '';

	protected \PHPFUI\ORM\FieldDefinition $currentFieldDefinitions;

	protected bool $currentNot = false;

	/** @var array<string> */
	protected array $currentParameters = [];

	protected bool $currentRequired = false;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected array $fieldDefinitions = [];

	/** @var array<string, array<string>> */
	private array $errors = [];

	public function __construct(protected \PHPFUI\ORM\Record $record, protected ?\PHPFUI\ORM\Record $originalRecord = null)
		{
		$this->fieldDefinitions = $this->record->getFields();
		}

	/**
	 * Return any errors.
	 *
	 * @return array<string, array<string>>  indexed by field(s) with error and array of translated errors.
	 */
	public function getErrors() : array
		{
		return $this->errors;
		}

	/**
	 * Return true if the entire record validates
	 *
	 * @param  string $optionalMethod will be called if it matches an existing method.  This can be used
	 * to more complex checks that need more involved validations. The optionalMethod
	 * overrides the normal validation, so if you want the normal validations, the optionalMethod will
	 * need to call the validate function again itself without the optionalMethod parameter.
	 *
	 * @return bool true if valid
	 */
	public function validate(string $optionalMethod = '') : bool
		{
		$this->errors = [];

		if ($optionalMethod && \method_exists($this, $optionalMethod))
			{
			$this->errors = $this->{$optionalMethod}();

			return empty($this->errors);
			}

		foreach ($this->fieldDefinitions as $field => $fieldDefinitions)
			{
			$this->currentField = $field;
			$errors = $this->getFieldErrors($this->record->{$field}, static::$validators[$field] ?? [], $fieldDefinitions);

			if ($errors)
				{
				$this->errors[$field] = $errors;
				}
			}

		return empty($this->errors);
		}

	/**
	 * @param array<string, mixed> $values
	 */
	protected function testIt(bool $condition, string $token, array $values = []) : string
		{
		$a = (int)$condition;
		$b = (int)$this->currentNot;

		if ($condition xor $this->currentNot)
			{
			return '';
			}
		$token = '.validator.' . ($this->currentNot ? 'not.' : '') . $token;

		return \PHPFUI\ORM::trans($token, $values);
		}

	/**
	 * Gets the errors for a value with the record definition and associated validators
	 *
	 * @param array<string> $validators
	 *
	 * @return array<string> of errors of translated text
	 */
	private function getFieldErrors(mixed $value, array $validators, \PHPFUI\ORM\FieldDefinition $fieldDefinitions) : array
		{
		$errors = [];

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

		$orErrors = [];

		foreach ($validators as $validator)
			{
			// Implements OR logic, any rule passes, the whole rule passes
			$orValidators = \explode('|', (string)$validator);

			if (\count($orValidators) > 1)
				{
				$orErrors = [];

				foreach ($orValidators as $validator)
					{
					$error = $this->validateRule($validator, $value, $fieldDefinitions);

					if ($error)
						{
						$orErrors = \array_merge($orErrors, $error);
						}
					else
						{
						$orErrors = [];

						break;
						}
					}
				}
			else
				{
				$errors = \array_merge($errors, $this->validateRule($validator, $value, $fieldDefinitions));
				}
			}

		return \array_merge($errors, $orErrors);
		}

	private function validate_alpha(mixed $value) : string
		{
		return $this->testIt(\ctype_alpha((string)$value), 'alpha', ['value' => $value]);
		}

	private function validate_alpha_numeric(mixed $value) : string
		{
		return $this->testIt(\ctype_alnum((string)$value), 'alnum', ['value' => $value]);
		}

	private function validate_bool(mixed $value) : string
		{
		return $this->testIt(\ctype_digit((string)$value) && (0 == $value || 1 == $value), 'bool', ['value' => $value]);
		}

	private function validate_card(string $number) : string
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
		return $this->testIt(0 == $total % 10, 'card', ['value' => $number]);
		}

	private function validate_color(mixed $value) : string
		{
		$len = 0;

		$testValue = '#' == $value[0] ? \substr((string)$value, 1) : $value;

		if (\ctype_xdigit((string)$testValue))
			{
			$len = \strlen((string)$testValue);
			}

		return $this->testIt(3 == $len || 6 == $len, 'color', ['value' => $value]);
		}

	private function validate_contains(mixed $value) : string
		{
		$valid = false;

		foreach ($this->currentParameters as $text)
			{
			$valid |= \str_contains($value, $text);
			}

		return $this->testIt($valid, 'contains', ['value' => $value, 'set' => \implode(',', $this->currentParameters)]);
		}

	private function validate_cvv(mixed $value) : string
		{
		$int = (int)$value;

		return $this->testIt($int >= 100 && $int <= 9999, 'cvv', ['value' => $value]);
		}

	private function validate_date(mixed $value) : string
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

		return $this->testIt(\checkdate((int)($parts[$month] ?? 0), (int)($parts[$day] ?? 0), (int)$parts[$year]), 'date', ['value' => $value]);
		}

	private function validate_dateISO(mixed $value) : string
		{
		$year = 0;
		$month = 1;
		$day = 2;
		$parts = \explode('-', (string)$value);
		$year = \sprintf('%04d', (int)$parts[$year]);
		$month = \sprintf('%02d', (int)($parts[$month] ?? 0));
		$day = \sprintf('%02d', (int)($parts[$day] ?? 0));

		return $this->testIt(4 == \strlen($year) && 2 == \strlen($month) && 2 == \strlen($day) && \checkdate((int)$month, (int)$day, (int)$year), 'dateISO', ['value' => $value]);
		}

	private function validate_datetime(mixed $value) : string
		{
		if (\strpos((string)$value, 'T'))
			{
			$parts = \explode('T', (string)$value);
			}
		else
			{
			$parts = \explode(' ', (string)$value);
			}

		$error = $this->validate_date($parts[0]);

		if ($error)
			{
			return $error;
			}

		return $this->validate_time($parts[1] ?? '');
		}

	private function validate_day_month_year(mixed $value) : string
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

		return $this->testIt(\checkdate((int)($parts[$month] ?? 0), (int)$parts[$day], (int)($parts[$year] ?? 0)), 'day_month_year', ['value' => $value]);
		}

	private function validate_domain(mixed $value) : string
		{
		return $this->testIt(false !== \filter_var($value, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME), 'domain', ['value' => $value]);
		}

	private function validate_email(mixed $value) : string
		{
		return $this->testIt(false !== \filter_var($value, \FILTER_VALIDATE_EMAIL), 'email', ['value' => $value]);
		}

	private function validate_ends_with(mixed $value) : string
		{
		$valid = false;

		foreach ($this->currentParameters as $end)
			{
			$valid |= \str_ends_with($value, $end);
			}

		return $this->testIt($valid, 'ends_with', ['value' => $value, 'set' => \implode(',', $this->currentParameters)]);
		}

	private function validate_enum(mixed $value) : string
		{
		$valueUC = \strtoupper((string)$value);
		$parametersUC = [];

		foreach ($this->currentParameters as $enum)
			{
			$parametersUC[] = \strtoupper((string)$enum);
			}

		return $this->testIt(\in_array($valueUC, $parametersUC), 'enum', ['value' => $value, 'valid' => \implode(',', $this->currentParameters)]);
		}

	private function validate_enum_exact(mixed $value) : string
		{
		return $this->testIt(\in_array($value, $this->currentParameters), 'enum', ['value' => $value, 'valid' => \implode(',', $this->currentParameters)]);
		}

	private function validate_eq_field(mixed $value) : string
		{
		$field = $this->currentParameters[0] ?? '';
		$compare = $this->record[$field];

		return $this->testIt(empty($compare) || $value == $compare, 'eq_field', ['value' => $value, 'field' => $field, 'compare' => $compare]);
		}

	private function validate_equal(mixed $value) : string
		{
		$required = $this->currentParameters[0] ?? '';

		return $this->testIt($required == $value, 'equal', ['value' => $value, 'required' => $required]);
		}

	private function validate_gt_field(mixed $value) : string
		{
		$field = $this->currentParameters[0] ?? '';
		$compare = $this->record[$field];

		return $this->testIt(empty($compare) || $value > $compare, 'gt_field', ['value' => $value, 'field' => $field, 'compare' => $compare]);
		}

	private function validate_gte_field(mixed $value) : string
		{
		$field = $this->currentParameters[0] ?? '';
		$compare = $this->record[$field];

		return $this->testIt(empty($compare) || $value >= $compare, 'gte_field', ['value' => $value, 'field' => $field, 'compare' => $compare]);
		}

	private function validate_icontains(mixed $value) : string
		{
		$valid = false;
		$test = \strtolower($value);

		foreach ($this->currentParameters as $text)
			{
			$valid |= \str_contains($test, \strtolower($text));
			}

		return $this->testIt($valid, 'icontains', ['value' => $value, 'set' => \implode(',', $this->currentParameters)]);
		}

	private function validate_iends_with(mixed $value) : string
		{
		$valid = false;
		$test = \strtolower($value);

		foreach ($this->currentParameters as $end)
			{
			$valid |= \str_ends_with($test, \strtolower($end));
			}

		return $this->testIt($valid, 'iends_with', ['value' => $value, 'set' => \implode(',', $this->currentParameters)]);
		}

	private function validate_integer(mixed $value) : string
		{
		return $this->testIt(false !== \filter_var($value, \FILTER_VALIDATE_INT), 'integer', ['value' => $value]);
		}

	private function validate_istarts_with(mixed $value) : string
		{
		$valid = false;
		$test = \strtolower($value);

		foreach ($this->currentParameters as $start)
			{
			$valid |= \str_starts_with($test, \strtolower($start));
			}

		return $this->testIt($valid, 'istarts_with', ['value' => $value, 'set' => \implode(',', $this->currentParameters)]);
		}

	private function validate_lt_field(mixed $value) : string
		{
		$field = $this->currentParameters[0] ?? '';
		$compare = $this->record[$field];

		return $this->testIt(empty($compare) || $value < $compare, 'lt_field', ['value' => $value, 'field' => $field, 'compare' => $compare]);
		}

	private function validate_lte_field(mixed $value) : string
		{
		$field = $this->currentParameters[0] ?? '';
		$compare = $this->record[$field];

		return $this->testIt(empty($compare) || $value <= $compare, 'lte_field', ['value' => $value, 'field' => $field, 'compare' => $compare]);
		}

	private function validate_maxlength(mixed $value) : string
		{
		if ($this->currentFieldDefinitions->length <= 0)
			{
			return '';	// zero length fields can't have a max length test
			}

		$length = $this->currentParameters[0] ?? $this->currentFieldDefinitions->length;

		return $this->testIt(\strlen((string)$value) <= $length, 'maxlength', ['value' => $value, 'length' => $length]);
		}

	private function validate_maxvalue(mixed $value) : string
		{
		if (! isset($this->currentParameters[0]))
			{
			return '';
			}

		return $this->testIt($this->currentParameters[0] >= $value, 'maxvalue', ['value' => $value, 'max' => $this->currentParameters[0]]);
		}

	private function validate_minlength(mixed $value) : string
		{
		$length = $this->currentParameters[0] ?? $this->currentFieldDefinitions->length;

		return $this->testIt(\strlen((string)$value) >= $length, 'minlength', ['value' => $value, 'length' => $length]);
		}

	private function validate_minvalue(mixed $value) : string
		{
		if (! isset($this->currentParameters[0]))
			{
			return '';
			}

		return $this->testIt($this->currentParameters[0] <= $value, 'minvalue', ['value' => $value, 'min' => $this->currentParameters[0]]);
		}

	private function validate_month_day_year(mixed $value) : string
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

		return $this->testIt(\checkdate((int)$parts[$month], (int)($parts[$day] ?? 0), (int)($parts[$year] ?? 0)), 'month_day_year', ['value' => $value]);
		}

	private function validate_month_year(mixed $value) : string
		{
		$year = 1;
		$month = 0;
		$day = 1;
		$parts = \explode('/', \str_replace(self::$dateSeparators, '/', (string)$value));

		return $this->testIt(\checkdate((int)$parts[$month], $day, (int)($parts[$year] ?? 0)), 'month_year', ['value' => $value]);
		}

	private function validate_neq_field(mixed $value) : string
		{
		$field = $this->currentParameters[0] ?? '';
		$compare = $this->record[$field];

		return $this->testIt(empty($compare) || $value != $compare, 'neq_field', ['value' => $value, 'field' => $field, 'compare' => $compare]);
		}

	private function validate_not_equal(mixed $value) : string
		{
		$required = $this->currentParameters[0] ?? '';

		return $this->testIt($required != $value, 'not_equal', ['value' => $value, 'required' => $required]);
		}

	private function validate_number(mixed $value) : string
		{
		return $this->testIt(false !== \filter_var($value, \FILTER_VALIDATE_FLOAT), 'number', ['value' => $value]);
		}

	private function validate_required(mixed $value) : string
		{
		return $this->testIt(\strlen("{$value}") > 0, 'required');
		}

	private function validate_starts_with(mixed $value) : string
		{
		$valid = false;

		foreach ($this->currentParameters as $start)
			{
			$valid |= \str_starts_with($value, $start);
			}

		return $this->testIt($valid, 'starts_with', ['value' => $value, 'set' => \implode(',', $this->currentParameters)]);
		}

	private function validate_time(mixed $value) : string
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

	private function validate_unique(mixed $value) : string
		{
		$class = '\\' . \PHPFUI\ORM::$tableNamespace . '\\' . \PHPFUI\ORM::getBaseClassName($this->record->getTableName());
		$table = new $class();
		// look up the record in the table.  Can't be itself.
		$condition = new \PHPFUI\ORM\Condition();
		$primaryKeys = $this->record->getPrimaryKeys();

		if (1 == \count($primaryKeys))
			{
			$primaryKey = $primaryKeys[0];
			$condition->and($primaryKey, $this->record->{$primaryKey}, new \PHPFUI\ORM\Operator\NotEqual());
			}
		$field = $this->currentField;
		$condition->and($field, $this->record->{$field});

		while (\count($this->currentParameters))
			{
			$field = \array_shift($this->currentParameters);

			if (isset($this->fieldDefinitions[$field]))
				{
				$value = $this->record->{$field};

				if (\count($this->currentParameters))
					{
					$next = \array_shift($this->currentParameters);

					if (isset($this->fieldDefinitions[$next]))
						{
						\array_unshift($this->currentParameters, $next);
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

		return $this->testIt(0 == (\is_countable($table) ? \count($table) : 0), 'unique', ['value' => $this->record->{$this->currentField}]);
		}

	private function validate_url(mixed $value) : string
		{
		return $this->testIt(false !== \filter_var($value, \FILTER_VALIDATE_URL), 'url', ['value' => $value]);
		}

	private function validate_website(mixed $value) : string
		{
		$parts = \explode('://', \strtolower((string)$value));
		$error = 2 != \count($parts) || ! \in_array($parts[0], ['http', 'https']);

		return $this->testIt(! $error && false !== \filter_var($value, \FILTER_VALIDATE_URL), 'website', ['value' => $value]);
		}

	private function validate_year_month(mixed $value) : string
		{
		$year = 0;
		$month = 1;
		$day = 1;
		$parts = \explode('/', \str_replace(self::$dateSeparators, '/', (string)$value));

		return $this->testIt(\checkdate((int)($parts[$month] ?? 0), $day, (int)$parts[$year]), 'year_month', ['value' => $value]);
		}

	/**
	 * Validate one rule.
	 *
	 * @return array<string> of errors of translated text
	 */
	private function validateRule(string $validator, mixed $value, \PHPFUI\ORM\FieldDefinition $fieldDefinitions) : array
		{
		$this->currentFieldDefinitions = $fieldDefinitions;
		$this->currentNot = false;

		if ('!' == $validator[0])
			{
			$this->currentNot = true;
			$validator = \substr($validator, 1);
			}

		$parts = \explode(':', (string)$validator);

		$this->currentParameters = $errors = [];

		if (\count($parts) > 1)
			{
			$this->currentParameters = \explode(',', $parts[1]);
			}
		$validator = $parts[0];

		$method = 'validate_' . $validator;

		if (\method_exists($this, $method))
			{
			$error = $this->{$method}($value);

			if ($error)
				{
				$errors[] = $error;
				}
			}
		else
			{
			throw new \Exception("Validator {$validator} (validate_{$validator} method) not found in class " . self::class);
			}

		return $errors;
		}
	}
