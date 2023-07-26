<?php

namespace PHPFUI\ORM;

abstract class VirtualField
	{
	public function __construct(protected \PHPFUI\ORM\Record $currentRecord, protected string $fieldName)
		{
		}

	/**
	 * @param array<mixed> $parameters optional
	 */
	public function getValue(array $parameters) : mixed
		{
		throw new \PHPFUI\ORM\Exception("get not defined for {$this->currentRecord->getTableName()}.{$this->fieldName}");
		}

	/**
	 * @param array<mixed> $parameters optional
	 */
	public function setValue(mixed $value, array $parameters) : void
		{
		throw new \PHPFUI\ORM\Exception("set not defined for {$this->currentRecord->getTableName()}.{$this->fieldName}");
		}
	}
