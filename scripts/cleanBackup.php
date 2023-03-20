<?php

include __DIR__ . '/../vendor/autoload.php';

echo "Clean up MySQL backup to correct char sets and collation\n\n";

if (3 != \count($argv))
	{
	echo "Incorrect number of parameters, two required\n\n";
	echo "Syntax: cleanBackup.php backup.sql newFile.sql\n";

	exit;
	}

\array_shift($argv);
$backupPath = \array_shift($argv);
$targetPath = \array_shift($argv);

if (! \file_exists($backupPath))
	{
	echo "File {$backupPath} was not found\n";

	exit;
	}

if (\file_exists($targetPath))
	{
	echo "File {$targetPath} already exists\n";

//	exit;
	}

$backupHandle = \fopen($backupPath, 'r');

if (! $backupHandle)
	{
	echo "Can't open {$backupPath} for reading\n";

	exit;
	}

$targetHandle = \fopen($targetPath, 'w');

if (! $targetHandle)
	{
	echo "Can't open {$targetPath} for writing\n";

	exit;
	}

$cleaner = new CleanBackup($backupHandle, $targetHandle);
$cleaner->run();
