<?php

namespace PHPFUI\ORM;

class RelatedRecord extends \PHPFUI\ORM\VirtualField
	{
	public function getValue(array $parameters) : mixed
		{
		$class = \array_shift($parameters);

		return new $class($this->currentRecord[$this->fieldName . \PHPFUI\ORM::$idSuffix]);
		}
	}
