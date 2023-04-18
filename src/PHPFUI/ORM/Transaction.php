<?php

namespace PHPFUI\ORM;

class Transaction
	{
	private bool $committed = false;

	private \PHPFUI\ORM\PDOInstance $instance;

	public function __construct()
		{
		$this->instance = \PHPFUI\ORM::getInstance();
		$this->instance->beginTransaction();
		}

	public function __destruct()
		{
		$this->rollBack();
		}

	public function rollBack() : bool
		{
		if ($this->committed)
			{
			return false;
			}

		return $this->committed = $this->instance->rollBack();
		}

	public function commit() : bool
		{
		return $this->committed = $this->instance->commit();
		}
	}
