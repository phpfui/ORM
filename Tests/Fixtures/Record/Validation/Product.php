<?php

namespace Tests\Fixtures\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class Product extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'category' => ['maxlength'],
		'description' => ['maxlength'],
		'list_price' => ['required', 'number'],
		'minimum_reorder_quantity' => ['integer'],
		'product_code' => ['maxlength', 'unique'],
		'product_id' => ['required', 'integer'],
		'product_name' => ['maxlength'],
		'quantity_per_unit' => ['maxlength'],
		'reorder_level' => ['integer'],
		'standard_cost' => ['number'],
		'target_level' => ['integer'],
	];
	}
