<?php

namespace Tests\Fixtures\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $date_allocated MySQL type datetime
 * @property float $discount MySQL type double
 * @property ?int $inventory_transaction_id MySQL type integer
 * @property \Tests\App\Record\InventoryTransaction $inventory_transaction_ related record
 * @property int $order_detail_id MySQL type integer
 * @property \Tests\Fixtures\Record\OrderDetail $order_detail_ related record
 * @property ?int $order_detail_status_id MySQL type integer
 * @property \Tests\Fixtures\Record\OrderDetailStatus $order_detail_status_ related record
 * @property int $order_id MySQL type integer
 * @property \Tests\App\Record\Order $order_ related record
 * @property ?int $product_id MySQL type integer
 * @property \Tests\App\Record\Product $product_ related record
 * @property ?int $purchase_order_id MySQL type integer
 * @property \Tests\App\Record\PurchaseOrder $purchase_order_ related record
 * @property float $quantity MySQL type decimal(18,4)
 * @property ?float $unit_price MySQL type decimal(19,4)
 */
abstract class OrderDetail extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, array<mixed>> */
	protected static array $fields = [
		// MYSQL_TYPE, PHP_TYPE, LENGTH, KEY, ALLOWS_NULL, DEFAULT
		'date_allocated' => ['datetime', 'string', 20, false, true, 'NULL', ],
		'discount' => ['double', 'float', 0, false, false, 0, ],
		'inventory_transaction_id' => ['integer', 'int', 0, false, true, null, ],
		'order_detail_id' => ['integer', 'int', 0, true, false, ],
		'order_detail_status_id' => ['integer', 'int', 0, false, true, null, ],
		'order_id' => ['integer', 'int', 0, false, false, ],
		'product_id' => ['integer', 'int', 0, false, true, null, ],
		'purchase_order_id' => ['integer', 'int', 0, false, true, null, ],
		'quantity' => ['decimal(18,4)', 'float', 18, false, false, 0, ],
		'unit_price' => ['decimal(19,4)', 'float', 19, false, true, 0, ],
	];

	/** @var array<string> */
	protected static array $primaryKeys = ['order_detail_id', ];

	protected static string $table = 'order_detail';
	}
