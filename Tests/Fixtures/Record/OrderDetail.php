<?php

namespace Tests\Fixtures\Record;

class OrderDetail extends \Tests\Fixtures\Record\Definition\OrderDetail
	{
	protected static array $virtualFields = [
		'gross' => [\Tests\Fixtures\Virtual\Gross::class],
	];
	}
