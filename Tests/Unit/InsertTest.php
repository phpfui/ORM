<?php

namespace Tests\Unit;

class InsertTest extends \PHPUnit\Framework\TestCase
	{
	public function testRecordInsert() : void
		{
		$customer = new \Tests\App\Record\Customer();
		$customer->address = '123 Broadway';
		$customer->business_phone = '212-987-6543';
		$customer->city = 'New York';
		$customer->company = 'PHPFUI';
		$customer->country_region = 'USA';
		$customer->email_address = 'bruce@phpfui.com';
		$customer->fax_number = '212-345-6789';
		$customer->first_name = 'Bruce';
		$customer->home_phone = '987-654-3210';
		$customer->job_title = 'Head Honcho';
		$customer->last_name = 'Wells';
		$customer->mobile_phone = '123-456-7890';
		$customer->state_province = 'NY';
		$customer->web_page = 'http://www.phpfui.com';
		$customer->zip_postal_code = '10021';

		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $table->count());
		$this->assertTrue(\PHPFUI\ORM::beginTransaction());
		$this->assertEquals(30, $customer->insert());
		$this->assertEquals(30, $table->count());
		$this->assertTrue(\PHPFUI\ORM::rollBack());
		$this->assertEquals(29, $table->count());
		}

	public function testRelatedInsert() : void
		{
		$customerTable = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $customerTable->count());
		$orderTable = new \Tests\App\Table\Order();
		$this->assertEquals(48, $orderTable->count());

		$this->assertTrue(\PHPFUI\ORM::beginTransaction());

		$customer = new \Tests\App\Record\Customer();
		$customer->address = '123 Broadway';
		$customer->business_phone = '212-987-6543';
		$customer->city = 'New York';
		$customer->company = 'PHPFUI';
		$customer->country_region = 'USA';
		$customer->email_address = 'bruce@phpfui.com';
		$customer->fax_number = '212-345-6789';
		$customer->first_name = 'Bruce';
		$customer->home_phone = '987-654-3210';
		$customer->job_title = 'Head Honcho';
		$customer->last_name = 'Wells';
		$customer->mobile_phone = '123-456-7890';
		$customer->state_province = 'NY';
		$customer->web_page = 'http://www.phpfui.com';
		$customer->zip_postal_code = '10021';

		$order = new \Tests\App\Record\Order();
		$order->employee_id = 9;
		$order->customer = $customer;
		$this->assertEquals(30, $customerTable->count());
		$this->assertEquals(30, $order->customer_id);
		$date = \date('Y-m-d H:i:s');
		$order->order_date = $date;
		$shipper = new \Tests\App\Record\Shipper();
		$shipper->read(['company' => 'Shipping Company B']);
		$this->assertEquals(2, $shipper->shipper_id);
		$order->shipper = $shipper;
		$this->assertEquals($shipper->shipper_id, $order->shipper_id);
		$order->ship_name = $customer->company;
		$order->ship_address = $customer->address;
		$order->ship_city = $customer->city;
		$order->ship_state_province = $customer->state_province;
		$order->ship_zip_postal_code = $customer->zip_postal_code;
		$order->ship_country_region = $customer->country_region;
		$order->shipping_fee = 12.95;
		$order->taxes = 2.37;
		$order->payment_type = 'PO 54321';
		$order->notes = 'Test Order';
		$order->tax_rate = 5.25;
		$order->order_tax_status_id = 1;
		$order->order_status_id = 1;
		$this->assertEquals(82, $order->insert());
		$this->assertEquals(49, $orderTable->count());

		$orderTable->setWhere(new \PHPFUI\ORM\Condition('notes', 'Test Order'));
		$foundOrder = $orderTable->getRecordCursor()->current();

		$this->assertTrue(\PHPFUI\ORM::rollBack());
		$this->assertEquals(29, $customerTable->count());
		$orderTable->setWhere();
		$this->assertEquals(48, $orderTable->count());
		}
	}
