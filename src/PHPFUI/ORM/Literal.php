<?php

namespace PHPFUI\ORM;

/**
 * A class representing a SQL literal statement.  No processing is done on it.
 */
class Literal implements \Stringable
	{
	public function __construct(private readonly string $name)
		{
		}

	public function __toString() : string
		{
		return $this->name;
		}
	}
