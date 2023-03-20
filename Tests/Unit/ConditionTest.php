<?php

namespace Tests\Unit;

class ConditionTest extends \PHPUnit\Framework\TestCase
	{
	public function testAnd() : void
		{
		$condition = new \PHPFUI\ORM\Condition('fred', 'fred');
		$condition->and('ethyl', 'alice', new \PHPFUI\ORM\Operator\NotEqual());
		$condition->and('ethyl', 'tom%', new \PHPFUI\ORM\Operator\Like());

		$sql = "{$condition}";
		$this->assertEquals('`fred` = ? AND `ethyl` <> ? AND `ethyl` LIKE ?', $sql, 'Generated Or Not condition incorrect');

		$fields = $condition->getFields();
		$input = $condition->getInput();
		$this->assertCount(\count($fields), $input, 'Different field and input counts');

		foreach (['fred', 'ethyl'] as $field)
			{
			$this->assertContains($field, $fields, $field . ' field not present');
			}

		foreach (['fred', 'alice', 'tom%'] as $field)
			{
			$this->assertContains($field, $input, $field . ' input not present');
			}

		$condition2 = new \PHPFUI\ORM\Condition('fred', 'tom');
		$condition2->and('ethyl', 'dick', new \PHPFUI\ORM\Operator\NotEqual());
		$condition2->and('ethyl', 'harry', new \PHPFUI\ORM\Operator\Like());
		$condition->and($condition2);

		$sql = "{$condition}";
		$this->assertEquals('`fred` = ? AND `ethyl` <> ? AND `ethyl` LIKE ? AND (`fred` = ? AND `ethyl` <> ? AND `ethyl` LIKE ?)', $sql, 'Generated Or Not condition incorrect');

		$fields = $condition->getFields();
		$input = $condition->getInput();
		$this->assertCount(\count($fields), $input, 'Different field and input counts');

		foreach (['fred', 'alice', 'tom%', 'tom', 'dick', 'harry'] as $field)
			{
			$this->assertContains($field, $input, $field . ' input not present');
			}
		}

	public function testAndNot() : void
		{
		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph');
		$condition->andNot('ethyl', 'alice', new \PHPFUI\ORM\Operator\NotEqual());

		$sql = "{$condition}";
		$this->assertEquals('`fred` = ? AND NOT `ethyl` <> ?', $sql, 'Generated Or Not condition incorrect');

		$fields = $condition->getFields();
		$input = $condition->getInput();
		$this->assertCount(\count($fields), $input, 'Different field and input counts');
		$this->assertContains('fred', $fields, 'fred field not present');
		$this->assertContains('ethyl', $fields, 'ethyl field not present');
		$this->assertContains('ralph', $input, 'ralph input not present');
		$this->assertContains('alice', $input, 'alice input not present');
		}

	public function testOperatorTypes() : void
		{
		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph');
		$sql = "{$condition}";
		$this->assertEquals('`fred` = ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph', new \PHPFUI\ORM\Operator\NotEqual());
		$sql = "{$condition}";
		$this->assertEquals('`fred` <> ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph', new \PHPFUI\ORM\Operator\GreaterThan());
		$sql = "{$condition}";
		$this->assertEquals('`fred` > ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph', new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$sql = "{$condition}";
		$this->assertEquals('`fred` >= ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph', new \PHPFUI\ORM\Operator\LessThan());
		$sql = "{$condition}";
		$this->assertEquals('`fred` < ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph', new \PHPFUI\ORM\Operator\LessThanEqual());
		$sql = "{$condition}";
		$this->assertEquals('`fred` <= ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', 'tom', new \PHPFUI\ORM\Operator\Like());
		$sql = "{$condition}";
		$this->assertEquals('`fred` LIKE ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', 'tom', new \PHPFUI\ORM\Operator\NotLike());
		$sql = "{$condition}";
		$this->assertEquals('`fred` NOT LIKE ?', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', ['tom', 'dick', 'hary'], new \PHPFUI\ORM\Operator\In());
		$sql = "{$condition}";
		$this->assertEquals('`fred` IN (?,?,?)', $sql, 'Generated condition incorrect');

		$condition = new \PHPFUI\ORM\Condition('fred', ['tom', 'dick', 'hary'], new \PHPFUI\ORM\Operator\NotIn());
		$sql = "{$condition}";
		$this->assertEquals('`fred` NOT IN (?,?,?)', $sql, 'Generated condition incorrect');
		}

	public function testOr() : void
		{
		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph');
		$condition->or('ethyl', 'alice', new \PHPFUI\ORM\Operator\NotEqual());

		$sql = "{$condition}";
		$this->assertEquals('`fred` = ? OR `ethyl` <> ?', $sql, 'Generated Or Not condition incorrect');

		$fields = $condition->getFields();
		$input = $condition->getInput();
		$this->assertCount(\count($fields), $input, 'Different field and input counts');
		$this->assertContains('fred', $fields, 'fred field not present');
		$this->assertContains('ethyl', $fields, 'ethyl field not present');
		$this->assertContains('ralph', $input, 'ralph input not present');
		$this->assertContains('alice', $input, 'alice input not present');
		}

	public function testOrNot() : void
		{
		$condition = new \PHPFUI\ORM\Condition('fred', 'ralph');
		$condition->orNot('ethyl', 'alice', new \PHPFUI\ORM\Operator\NotEqual());

		$sql = "{$condition}";
		$this->assertEquals('`fred` = ? OR NOT `ethyl` <> ?', $sql, 'Generated Or Not condition incorrect');

		$fields = $condition->getFields();
		$input = $condition->getInput();
		$this->assertCount(\count($fields), $input, 'Different field and input counts');
		$this->assertContains('fred', $fields, 'fred field not present');
		$this->assertContains('ethyl', $fields, 'ethyl field not present');
		$this->assertContains('ralph', $input, 'ralph input not present');
		$this->assertContains('alice', $input, 'alice input not present');
		}
	}
