<?php

namespace Tests\Fixtures\Virtual;

class Gross extends \PHPFUI\ORM\VirtualField
	{
	public function getValue(array $parameters) : mixed
		{
		return \number_format($this->parentRecord->unit_price * $this->parentRecord->quantity - $this->parentRecord->discount, 2);
		}
	}
