<?php

namespace Tests\Unit;

class DeleteTest extends \PHPUnit\Framework\TestCase
	{
	public function testDeleteChildren() : void
		{
		$transaction = new \PHPFUI\ORM\Transaction();
		$purchaseOrder = new \Tests\Fixtures\Record\PurchaseOrder(93);
		$this->assertCount(3, $purchaseOrder->purchaseOrderDetailChildren);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$purchaseOrderDetailTable = new \Tests\Fixtures\Table\PurchaseOrderDetail();
		$purchaseOrderDetailTable->setWhere(new \PHPFUI\ORM\Condition('purchase_order_id', 93));
		$this->assertCount(3, $purchaseOrderDetailTable);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$purchaseOrder->delete();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertCount(0, $purchaseOrderDetailTable);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertTrue($transaction->rollBack());
		$purchaseOrder = new \Tests\Fixtures\Record\PurchaseOrder(93);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertTrue($purchaseOrder->loaded());
		$this->assertCount(3, $purchaseOrder->purchaseOrderDetailChildren);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testRecordDelete() : void
		{
		$transaction = new \PHPFUI\ORM\Transaction();
		$table = new \Tests\App\Table\PurchaseOrderDetail();
		$this->assertEquals(55, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$purchaseorderdetail = new \Tests\App\Record\PurchaseOrderDetail(245);
		$this->assertEquals('2006-01-22 00:00:00', $purchaseorderdetail->date_received);
		$purchaseorderdetail->delete();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(54, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertTrue($transaction->rollback());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(55, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testTableDelete() : void
		{
		$transaction = new \PHPFUI\ORM\Transaction();
		$table = new \Tests\App\Table\PurchaseOrderDetail();
		$this->assertEquals(55, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$table->setWhere(new \PHPFUI\ORM\Condition('purchase_order_detail_id', 9));
		$table->delete();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(0, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$table = new \Tests\App\Table\PurchaseOrderDetail();
		$this->assertEquals(55, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(55, $table->count());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}
	}
