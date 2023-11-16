<?php

namespace Tests\Unit;

class ExplainTest extends \PHPUnit\Framework\TestCase
	{
	public function testExplainGroupBy() : void
		{
		$table = new \Tests\App\Table\InventoryTransaction();
		$table->addSelect('category');
		$table->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');
		$table->addJoin('product');
		$table->addGroupBy('category');
		$table->addOrderBy('count', 'desc');
		$this->assertGreaterThan(0, \count($table->getExplainRows()));
		}

	public function testExplainIn() : void
		{
		$orderDetailTable = new \Tests\App\Table\OrderDetail();
		$orderDetailTable->setWhere(new \PHPFUI\ORM\Condition('quantity', 10));
		$orderDetailTable->addSelect('order_id');
		$this->assertGreaterThan(0, \count($orderDetailTable->getExplainRows()));
		$orderTable = new \Tests\App\Table\Order();
		$orderTable->setWhere(new \PHPFUI\ORM\Condition('order_id', $orderDetailTable, new \PHPFUI\ORM\Operator\In()));
		$this->assertGreaterThan(0, \count($orderTable->getExplainRows()));
		}

	public function testExplainJoin() : void
		{
		$table = new \Tests\App\Table\InventoryTransaction();
		$table->addJoin('inventory_transaction_type');
		$table->addJoin('order');
		$table->addJoin('product');
		$table->addJoin('purchase_order');

		$table->setLimit(10);
		$this->assertGreaterThan(0, \count($table->getExplainRows()));
		}

	public function testExplainNotIn() : void
		{
		$orderDetailTable = new \Tests\App\Table\OrderDetail();
		$orderDetailTable->setWhere(new \PHPFUI\ORM\Condition('quantity', 10));
		$orderDetailTable->addSelect('order_id');
		$this->assertGreaterThan(0, \count($orderDetailTable->getExplainRows()));
		$orderTable = new \Tests\App\Table\Order();
		$orderTable->setWhere(new \PHPFUI\ORM\Condition('order_id', $orderDetailTable, new \PHPFUI\ORM\Operator\NotIn()));
		$this->assertGreaterThan(0, \count($orderTable->getExplainRows()));
		}

	public function testExplainUnion() : void
		{
		$table = new \Tests\App\Table\InventoryTransactionType();
		$table->addSelect('inventory_transaction_type_id', 'id');
		$table->addSelect('inventory_transaction_type_name', 'name');
		$table->addUnion(new \Tests\App\Table\OrderDetailStatus());
		$table->addUnion(new \Tests\App\Table\OrderStatus());
		$table->addUnion(new \Tests\App\Table\OrderTaxStatus());
		$table->addUnion(new \Tests\App\Table\PurchaseOrderStatus());
		$table->addOrderBy('name');
		$this->assertGreaterThan(0, \count($table->getExplainRows()));
		}

	public function testExplainWhere() : void
		{
		$table = new \Tests\App\Table\Customer();
		$table->setWhere(new \PHPFUI\ORM\Condition('job_title', 'Purchasing Manager'));
		$this->assertGreaterThan(0, \count($table->getExplainRows()));

		$table->setWhere(new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\Like()));
		$this->assertGreaterThan(0, \count($table->getExplainRows()));

		$table->setWhere(new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\NotLike()));
		$this->assertGreaterThan(0, \count($table->getExplainRows()));

		$condition = new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\Like());
		$condition->and(new \PHPFUI\ORM\Condition('state_province', 'NY'));
		$table->setWhere($condition);
		$this->assertGreaterThan(0, \count($table->getExplainRows()));

		$condition = new \PHPFUI\ORM\Condition('job_title', '%Purchasing%', new \PHPFUI\ORM\Operator\NotLike());
		$condition->or(new \PHPFUI\ORM\Condition('state_province', 'NY'));
		$table->setWhere($condition);
		$this->assertGreaterThan(0, \count($table->getExplainRows()));
		}
	}
