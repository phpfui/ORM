<?php

namespace Tests\Unit;

class JoinTest extends \PHPUnit\Framework\TestCase
	{
	public function testBadJoin() : void
		{
		$this->expectException(\PHPFUI\ORM\Exception::class);
		$table = new \Tests\App\Table\InventoryTransaction();
		$table->addJoin('employee');
		}

	public function testBadJoinType() : void
		{
		$this->expectException(\PHPFUI\ORM\Exception::class);
		$table = new \Tests\App\Table\InventoryTransaction();
		$table->addJoin('inventory_transaction_type');
		$table->addJoin('fred', type:'fred');
		}

	public function testBadTableJoin() : void
		{
		$this->expectException(\PHPFUI\ORM\Exception::class);
		$table = new \Tests\App\Table\InventoryTransaction();
		$table->addJoin('fred');
		}
	}
