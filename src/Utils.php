<?php

namespace beemaker;

class Utils
{
	/** @var string */
	private static $folder;

	/** @var bool */
	private static $silent = false;

	public static function setFolder(string $folder): void
	{
		self::$folder = $folder;
	}

	/**
	 * Outputs a message unless silent mode is enabled.
	 */
	public static function echo(string $message): void
	{
		if (!self::$silent) {
			echo $message;
		}
	}

	public static function basePath()
	{
		$basePath = getcwd();

		return $basePath;
	}
}