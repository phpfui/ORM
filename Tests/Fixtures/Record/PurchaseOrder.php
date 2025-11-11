<?php

namespace Tests\Fixtures\Record;

class PurchaseOrder extends \Tests\Fixtures\Record\Definition\PurchaseOrder
	{
	protected static array $virtualFields = [
		'purchaseOrderDetailChildren' => [\PHPFUI\ORM\Children::class, \Tests\Fixtures\Table\PurchaseOrderDetail::class, 'purchase_order_detail_id'],
		'submitted_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
		'creation_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
		'expected_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
		'payment_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
		'approved_date' => [\PHPFUI\ORM\Cast::class, \Carbon\Carbon::class],
	];
	}
