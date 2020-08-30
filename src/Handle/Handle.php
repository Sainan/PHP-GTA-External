<?php
namespace V\Handle;
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
}
