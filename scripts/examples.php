<?php

/**
 * Usage examples
 *
 * You must run unit tests first to generate the models
 */

include __DIR__ . '/../vendor/autoload.php';

// create the PDO object to use
$pdo = new \PHPFUI\ORM\PDOInstance('sqlite:' . __DIR__ . '/../northwind/northwind.db');

// register the PDO object with the ORM
\PHPFUI\ORM::addConnection($pdo);

// Set model directories
\PHPFUI\ORM::$namespaceRoot = __DIR__ . '/..';
\PHPFUI\ORM::$recordNamespace = 'Tests\\App\\Record';
\PHPFUI\ORM::$tableNamespace = 'Tests\\App\\Table';
\PHPFUI\ORM::$migrationNamespace = 'Tests\\Fixtures\\Migration';

// specify the related record id format
\PHPFUI\ORM::$idSuffix = '_id';

// add English translations for the error messages
\PHPFUI\ORM::setTranslationCallback(\PHPFUI\Translation\Translator::trans(...));
\PHPFUI\Translation\Translator::setTranslationDirectory(__DIR__ . '/../translations');
\PHPFUI\Translation\Translator::setLocale('en_US');

// Simple select example
$customerTable = new \Tests\App\Table\Customer();

echo "\n\nCustomers in default data order:\n\n";
foreach ($customerTable->getRecordCursor() as $customer)
	{
	echo "{$customer->first_name} {$customer->last_name}, {$customer->job_title}\n";
	}

$customerTable->addOrderBy('last_name');
echo "\n\nCustomers in last_name order:\n\n";
foreach ($customerTable->getRecordCursor() as $customer)
	{
	echo "{$customer->first_name} {$customer->last_name}, {$customer->job_title}\n";
	}

$customerTable->addOrderBy('last_name', 'desc');
$customerTable->setLimit(10);

echo "\n\nLast 10 Customers in last_name order:\n\n";
foreach ($customerTable->getRecordCursor() as $customer)
	{
	echo "{$customer->first_name} {$customer->last_name}, {$customer->job_title}\n";
	}

$customerTable->setLimit(0);
$customerTable->addGroupBy('company');
$customerTable->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');
$customerTable->addSelect('company');
$customerTable->setOrderBy('company');
echo "\n\nCount of Customers by company:\n\n";

foreach ($customerTable->getDataObjectCursor() as $customer)
	{
	echo "{$customer->company}: {$customer->count}\n";
	}


$orderTable = new \Tests\App\Table\Order();
// addJoin defaults to using the primary key of the related table
$orderTable->addJoin('customer');
$orderTable->addJoin('employee');
$orderTable->addJoin('order_status');

echo "\n\nOrders with customer, employee and order status joins:\n\n";
foreach ($orderTable->getDataObjectCursor() as $order)
	{
	echo "Order {$order->order_id}: Sold to: {$order->company} Sold by: {$order->first_name} {$order->last_name}, Status: {$order->order_status_name}\n";
	}


