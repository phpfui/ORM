<?php

declare(strict_types = 1);

namespace PHPFUI\ORM;

class ConsoleErrorLogger extends \Psr\Log\AbstractLogger
	{
	/**
	 * Logs with an arbitrary level to standard error
	 */
	public function log($level, \Stringable | string $message, array $context = []) : void
		{
		if ($context)
			{
			$message .= \print_r($context, true);
			}
		$fullMessage = "{$level} Error: {$message}";
		echo $fullMessage . "\n";
		\error_log($fullMessage);
		}
	}
