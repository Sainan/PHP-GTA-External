<?php
namespace V;
class Handle
{
	public int $handle;

	function __construct(int $handle)
	{
		$this->handle = $handle;
	}

	function __destruct()
	{
		Kernel32::CloseHandle($this->handle);
	}
}
