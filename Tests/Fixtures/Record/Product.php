<?php

namespace Tests\Fixtures\Record;

class Product extends \Tests\Fixtures\Record\Definition\Product
	{
	protected static array $virtualFields = [
		'suppliers' => [\PHPFUI\ORM\ManyToMany::class, \Tests\App\Table\ProductSupplier::class, \Tests\App\Table\Supplier::class],
	];
	}
