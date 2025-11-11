<?php

namespace Tests\Unit;

enum TestEnum : int
	{
	case NO = 0;
	case YES = 1;
	}

class MiscellaneousTest extends \PHPUnit\Framework\TestCase
	{
	public function testEnumCondition() : void
		{
		$condition = new \PHPFUI\ORM\Condition('field', TestEnum::YES);
		$input = $condition->getInput();
		$this->assertIsArray($input);
		$this->assertCount(1, $input);
		$this->assertContains(1, $input);
		}

	public function testInOperator() : void
		{
		$in = new \PHPFUI\ORM\Operator\In();
		$this->assertTrue($in->correctlyTyped([1, 2]));
		$this->assertTrue($in->correctlyTyped([]));
		$this->assertFalse($in->correctlyTyped(1));

		$notIn = new \PHPFUI\ORM\Operator\NotIn();
		$this->assertTrue($notIn->correctlyTyped([1, 2]));
		$this->assertTrue($notIn->correctlyTyped([]));
		$this->assertFalse($notIn->correctlyTyped(1));
		}

	public function testInSelectOperator() : void
		{
		$productTable = new \Tests\Fixtures\Table\Product();
		$productTable->addSelect('product_id');
		$productTable->setWhere(new \PHPFUI\ORM\Condition('product_name', '%dried%', new \PHPFUI\ORM\Operator\Like()));
		$orderDetailTable = new \Tests\Fixtures\Table\OrderDetail();
		$orderDetailTable->setWhere(new \PHPFUI\ORM\Condition('product_id', $productTable, new \PHPFUI\ORM\Operator\In()));

		$this->assertCount(8, $orderDetailTable);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$orderDetailTable->setWhere(new \PHPFUI\ORM\Condition('product_id', $productTable, new \PHPFUI\ORM\Operator\NotIn()));
		$this->assertCount(50, $orderDetailTable);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testLiteralCondition() : void
		{
		$condition = new \PHPFUI\ORM\Condition('field', new \PHPFUI\ORM\Literal('invoiceItem.storeItemId'));
		$this->assertEquals('field = invoiceItem.storeItemId', (string)$condition);
		}

	public function testNullAssignment() : void
		{
		$po = new \Tests\App\Record\PurchaseOrder();
		$po->payment_method = 'Cash';
		$this->assertNotNull($po->payment_method);
		$po->payment_method = null;
		$this->assertNull($po->payment_method);
		}

	public function testNullAssignmentError() : void
		{
		$po = new \Tests\App\Record\PurchaseOrder();
		$this->assertIsFloat($po->shipping_fee);
		$this->assertEquals(0.0, $po->shipping_fee);
		$this->expectException(\PHPFUI\ORM\Exception::class);
		$po->shipping_fee = null;
		}

	public function testRow() : void
		{
		$row = \PHPFUI\ORM::getRow('select * from customer');
		$this->assertIsArray($row);
		$this->assertCount(18, $row);
		$this->assertArrayHasKey('company', $row);
		$this->assertEquals('Company A', $row['company']);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testRows() : void
		{
		$rows = \PHPFUI\ORM::getRows('select * from customer');
		$this->assertIsArray($rows);
		$this->assertCount(29, $rows);
		$this->assertEquals('Company A', $rows[0]['company']);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testValue() : void
		{
		$row = \PHPFUI\ORM::getValue('select * from customer');
		$this->assertIsNotArray($row);
		$this->assertEquals(1, $row);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}

	public function testValueArray() : void
		{
		$rows = \PHPFUI\ORM::getValueArray('select * from customer');
		$this->assertIsArray($rows);
		$this->assertCount(29, $rows);
		$this->assertEquals(1, $rows[0]);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		}
	}
