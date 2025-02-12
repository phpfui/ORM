<?php

namespace Tests\Fixtures\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class Order extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'customer_id' => ['integer'],
		'employee_id' => ['integer'],
		'notes' => ['maxlength'],
		'order_date' => ['required', 'maxlength', 'datetime'],
		'order_id' => ['integer'],
		'order_status_id' => ['integer'],
		'order_tax_status_id' => ['integer'],
		'paid_date' => ['maxlength', 'datetime'],
		'payment_type' => ['maxlength'],
		'ship_address' => ['maxlength'],
		'ship_city' => ['maxlength'],
		'ship_country_region' => ['maxlength'],
		'ship_name' => ['maxlength'],
		'ship_state_province' => ['maxlength'],
		'ship_zip_postal_code' => ['maxlength'],
		'shipped_date' => ['maxlength', 'datetime'],
		'shipper_id' => ['integer'],
		'shipping_fee' => ['number'],
		'tax_rate' => ['number'],
		'taxes' => ['number'],
	];
	}
