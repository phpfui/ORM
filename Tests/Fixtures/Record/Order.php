<?php

namespace Tests\Fixtures\Record;

class Order extends \Tests\Fixtures\Record\Definition\Order
	{
	protected static array $virtualFields = [
		'OrderDetailChildren' => [\PHPFUI\ORM\Children::class, \Tests\Fixtures\Table\OrderDetail::class, 'order_detail_id'],
		'order_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
		'paid_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
		'shipped_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
	];
	}
