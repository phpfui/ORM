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
	}

$cleaner = new CleanBackup($backupPath, $targetPath);
if ($cleaner->run())
	{
	echo "File {$targetPath} cleaned\n";
	}
else
	{
	echo 'Error: ' . $cleaner->getError() . "\n";
	}
