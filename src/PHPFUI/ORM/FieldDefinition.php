<?php

namespace PHPFUI\ORM;

class FieldDefinition
	{
	public function __construct(
		public readonly string $sqlType,
		public readonly string $phpType,
		public readonly int $length,
		public readonly bool $nullable,
		public readonly mixed $defaultValue = null
	)
		{
		}
	}
