<?php

namespace PHPFUI\ORM;

abstract class VirtualField
	{
	public function __construct(protected \PHPFUI\ORM\Record $currentRecord, protected string $fieldName)
		{
		}

	/**
	 * Override to do a custom conversion from a base PHP type (string, int, float, bool, ect)
	 *
	 * @param array<mixed> $parameters optional
	 */
	public function fromPHPValue(mixed $value, array $parameters) : mixed
		{
		return $value;
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
