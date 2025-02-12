<?php

namespace Tests\Fixtures\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?string $date_allocated MySQL type datetime
 * @property float $discount MySQL type double
 * @property ?int $inventory_transaction_id MySQL type integer
 * @property \Tests\App\Record\InventoryTransaction $inventory_transaction related record
 * @property int $order_detail_id MySQL type integer
 * @property ?int $order_detail_status_id MySQL type integer
 * @property \Tests\App\Record\OrderDetailStatus $order_detail_status related record
 * @property int $order_id MySQL type integer
 * @property \Tests\App\Record\Order $order related record
 * @property ?int $product_id MySQL type integer
 * @property \Tests\App\Record\Product $product related record
 * @property ?int $purchase_order_id MySQL type integer
 * @property \Tests\App\Record\PurchaseOrder $purchase_order related record
 * @property float $quantity MySQL type decimal(18,4)
 * @property ?float $unit_price MySQL type decimal(19,4)
 */
abstract class OrderDetail extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['order_detail_id', ];

	protected static string $table = 'order_detail';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'date_allocated' => new \PHPFUI\ORM\FieldDefinition('datetime', 'string', 20, true, null, ),
				'discount' => new \PHPFUI\ORM\FieldDefinition('double', 'float', 0, false, 0, ),
				'inventory_transaction_id' => new \PHPFUI\ORM\FieldDefinition('integer', 'int', 0, true, null, ),
				'order_detail_id' => new \PHPFUI\ORM\FieldDefinition('integer', 'int', 0, false, ),
				'order_detail_status_id' => new \PHPFUI\ORM\FieldDefinition('integer', 'int', 0, true, null, ),
				'order_id' => new \PHPFUI\ORM\FieldDefinition('integer', 'int', 0, false, ),
				'product_id' => new \PHPFUI\ORM\FieldDefinition('integer', 'int', 0, true, null, ),
				'purchase_order_id' => new \PHPFUI\ORM\FieldDefinition('integer', 'int', 0, true, null, ),
				'quantity' => new \PHPFUI\ORM\FieldDefinition('decimal(18,4)', 'float', 18, false, 0.0000, ),
				'unit_price' => new \PHPFUI\ORM\FieldDefinition('decimal(19,4)', 'float', 19, true, 0.0000, ),
			];
			}

		return $this;
		}
	}
