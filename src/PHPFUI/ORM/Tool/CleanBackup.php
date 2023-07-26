<?php

namespace PHPFUI\ORM\Tool;

class CleanBackup
	{
	/** @var resource */
	private $backupHandle;

	private string $error = '';

	/** @var resource */
	private $targetHandle;

	public function __construct(string $backupPath, string $targetPath)
		{
		$this->backupHandle = \fopen($backupPath, 'r');

		if (! $this->backupHandle)
			{
			$this->error = "Can't open {$backupPath} for reading";

			return;
			}

		$this->targetHandle = \fopen($targetPath, 'w');

		if (! $this->targetHandle)
			{
			$this->error = "Can't open {$targetPath} for writing";

			return;
			}
		}

	public function getError() : string
		{
		return $this->error;
		}

	public function run() : bool
		{
		if ($this->error)
			{
			return false;
			}

		while (($line = \fgets($this->backupHandle)) !== false)
			{
			\fwrite($this->targetHandle, $this->processLine($line));
			}

		return true;
		}

	private function processLine(string $line) : string
		{
		static $options = ['CHARSET=' => 'UTF8MB4', 'COLLATE ' => '', 'COLLATE=' => 'utf8mb4_general_ci', 'DEFINER=' => 'CURRENT_USER', ];

		foreach ($options as $option => $replacement)
			{
			$line = $this->replaceOption($option, $replacement, $line);
			}

		return $line;
		}

	private function replaceOption(string $option, string $replacement, string $line) : string
		{
		$start = \stripos($line, $option);

		if (false === $start)
			{
			return $line;
			}

		$lineEnd = \strlen($line);

		if (\strlen($replacement))
			{
			$start += \strlen($option);
			$end = $start;
			}
		else
			{
			$end = $start + \strlen($option);
			}

		while ($end < $lineEnd && ' ' != $line[$end] && ';' != $line[$end] && ',' != $line[$end])
			{
			++$end;
			}

		return \substr($line, 0, $start) . $replacement . \substr($line, $end);
		}
	}
