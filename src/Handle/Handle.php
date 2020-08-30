<?php
namespace V\Handle;
use V\Kernel32;
class Handle
{
	public const INVALID_HANDLE_VALUE = -1;

	public int $handle;

	function __construct(int $handle)
	{
		$this->handle = $handle;
	}

	function isValid() : bool
	{
		return $this->handle != self::INVALID_HANDLE_VALUE;
	}

	function __destruct()
	{
		if($this->isValid())
		{
			Kernel32::CloseHandle($this->handle);
		}
	}
}
