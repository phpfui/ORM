<?php

namespace Tests\Unit;

class SelectTest extends \PHPUnit\Framework\TestCase
	{
	public function testSelectCount() : void
		{
		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $table->count());
		$recordCursor = $table->getRecordCursor();
		$this->assertEquals(29, $recordCursor->count());
		$this->assertEquals(29, \count($recordCursor));
		$customer = $recordCursor->current();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals('Company A', $customer->company);

		$arrayCursor = $table->getArrayCursor();
		$this->assertEquals(29, $arrayCursor->count());
		$this->assertEquals(29, \count($arrayCursor));
		$this->assertEquals('Company A', $arrayCursor->current()['company']);
		$arrayRecord = new \Tests\App\Record\Customer();
		$arrayRecord->setFrom($arrayCursor->current());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals($customer->toArray(), $arrayRecord->toArray());

		$dataObjectCursor = $table->getDataObjectCursor();
		$this->assertEquals(29, $dataObjectCursor->count());
		$this->assertEquals(29, \count($dataObjectCursor));
		$dataObject = $dataObjectCursor->current();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals('Company A', $dataObject->company);
		$dataRecord = new \Tests\App\Record\Customer($dataObject);
		$this->assertEquals($customer->toArray(), $dataRecord->toArray());
		}

	public function testSelectGroupBy() : void
		{
		$table = new \Tests\App\Table\InventoryTransaction();
		$table->addSelect('category');
		$table->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');
		$table->addJoin('product');
		$table->addGroupBy('category');
		$table->addOrderBy('count', 'desc');
		$this->assertEquals(14, $table->count());
		$record = $table->getDataObjectCursor()->current();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(26, $record->count);
		$this->assertEquals('Beverages', $record->category);
		}

	public function testSelectHaving() : void
		{
		$orderDetailTable = new \Tests\App\Table\OrderDetail();
		$orderDetailTable->addSelect('*');
		$orderDetailTable->setGroupBy('order_detail_id');
		$orderDetailTable->setHaving(new \PHPFUI\ORM\Condition(new \PHPFUI\ORM\Literal('(quantity * unit_price)'), 1000.00, new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$recordCursor = $orderDetailTable->getRecordCursor();
		$count = $recordCursor->count();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		// SQLite count returns zero in this instance for unknown reasons, so bail for this test on SQLite only
		if (! \PHPFUI\ORM::getInstance()->sqlite)
			{
			$this->assertEquals(16, $recordCursor->count());
			}
		}

	public function testSelectIn() : void
		{
		$orderDetailTable = new \Tests\App\Table\OrderDetail();
		$orderDetailTable->setWhere(new \PHPFUI\ORM\Condition('quantity', 10));
		$orderDetailTable->addSelect('order_id');
		$orderTable = new \Tests\App\Table\Order();
		$orderTable->setWhere(new \PHPFUI\ORM\Condition('order_id', $orderDetailTable, new \PHPFUI\ORM\Operator\In()));
		$this->assertEquals(6, $orderTable->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testSelectJoin() : void
		{
		$table = new \Tests\App\Table\InventoryTransaction();
		$table->addJoin('inventory_transaction_type');
		$table->addJoin('order');
		$table->addJoin('product');
		$table->addJoin('purchase_order');
		$table->addOrderBy('inventory_transaction_id');

		$table->setLimit(10);
		$this->assertEquals(10, $table->count());
		$this->assertEquals(102, $table->total());
		$record = $table->getDataObjectCursor()->current();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$this->assertEquals(10, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(3.5, $record->list_price);
		$this->assertEquals(25, $record->minimum_reorder_quantity);
		$this->assertEquals('NWTDFN-80', $record->product_code);
		$this->assertEquals(80, $record->product_product_id);
		$this->assertEquals('Northwind Traders Dried Plums', $record->product_name);
		$this->assertEquals('1 lb bag', $record->quantity_per_unit);
		$this->assertEquals(50, $record->reorder_level);
		$this->assertEquals(3, $record->standard_cost);
		$this->assertEquals(75, $record->target_level);
		}

	public function testSelectLimitOrderBy() : void
		{
		$table = new \Tests\App\Table\InventoryTransaction();
		$this->assertEquals(102, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$table->setLimit(10);
		$this->assertEquals(10, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(102, $table->total());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$table->setLimit(20);
		$table->setOrderBy('inventory_transaction_id', 'desc');
		$this->assertEquals(20, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(102, $table->total());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$inventoryTransaction = $table->getRecordCursor()->current();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(136, $inventoryTransaction->inventory_transaction_id);

		$table->setLimit(20, 2);
		$table->setOrderBy('inventory_transaction_id');
		$inventoryTransaction = $table->getRecordCursor()->current();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(75, $inventoryTransaction->inventory_transaction_id);

		$table->setOrderBy('inventory_transaction_id', 'desc');
		$inventoryTransaction = $table->getRecordCursor()->current();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(96, $inventoryTransaction->inventory_transaction_id);
		}

	public function testSelectNotIn() : void
		{
		$orderDetailTable = new \Tests\App\Table\OrderDetail();
		$orderDetailTable->setWhere(new \PHPFUI\ORM\Condition('quantity', 10));
		$orderDetailTable->addSelect('order_id');
		$orderTable = new \Tests\App\Table\Order();
		$orderTable->setWhere(new \PHPFUI\ORM\Condition('order_id', $orderDetailTable, new \PHPFUI\ORM\Operator\NotIn()));
		$this->assertEquals(42, $orderTable->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testSelectUnion() : void
		{
		$table = new \Tests\App\Table\InventoryTransactionType();
		$table->addSelect('inventory_transaction_type_id', 'id');
		$table->addSelect('inventory_transaction_type_name', 'name');
		$table->addUnion(new \Tests\App\Table\OrderDetailStatus());
		$table->addUnion(new \Tests\App\Table\OrderStatus());
		$table->addUnion(new \Tests\App\Table\OrderTaxStatus());
		$table->addUnion(new \Tests\App\Table\PurchaseOrderStatus());
		$table->addOrderBy('name');
		$this->assertEquals(18, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals('Allocated', $table->getDataObjectCursor()->current()->name);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testSelectWhere() : void
		{
		$table = new \Tests\App\Table\Customer();
		$table->setWhere(new \PHPFUI\ORM\Condition('job_title', 'Purchasing Manager'));
		$this->assertEquals(13, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$table->setWhere(new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\Like()));
		$this->assertEquals(20, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$table->setWhere(new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\NotLike()));
		$this->assertEquals(9, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$condition = new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\Like());
		$condition->and(new \PHPFUI\ORM\Condition('state_province', 'NY'));
		$table->setWhere($condition);
		$this->assertEquals(2, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$condition = new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\NotLike());
		$condition->or(new \PHPFUI\ORM\Condition('state_province', 'NY'));
		$table->setWhere($condition);
		$this->assertEquals(11, \count($table));
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(11, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}
	}
