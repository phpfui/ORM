<?php

namespace Tests\Unit;

class DeleteTest extends \PHPUnit\Framework\TestCase
	{
	public function testRecordDelete() : void
		{
		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $table->count());
		$transaction = new \PHPFUI\ORM\Transaction();
		$customer = new \Tests\App\Record\Customer(9);
		$this->assertEquals('Company I', $customer->company);
		$customer->delete();
		$this->assertEquals(28, $table->count());
		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(29, $table->count());
		}

	public function testDeleteChildren() : void
		{
		$order = new \Tests\Fixtures\Record\Order(31);
		$this->assertCount(3, $order->orderDetailChildren);
		$transaction = new \PHPFUI\ORM\Transaction();
		$orderDetailTable = new \Tests\Fixtures\Table\OrderDetail();
		$orderDetailTable->setWhere(new \PHPFUI\ORM\Condition('order_id', 31));
		$this->assertCount(3, $orderDetailTable);
		$order->delete();
		$this->assertCount(0, $orderDetailTable);
		$this->assertTrue($transaction->rollBack());
		$this->assertCount(3, $orderDetailTable);
		}

	public function testTableDelete() : void
		{
		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $table->count());
		$transaction = new \PHPFUI\ORM\Transaction();
		$table->setWhere(new \PHPFUI\ORM\Condition('customer_id', 9));
		$table->delete();
		$this->assertEquals(0, $table->count());
		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(28, $table->count());
		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(29, $table->count());
		}
	}
