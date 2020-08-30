<?php
namespace V\Handle;
use V\Kernel32;
class ObjectHandle extends Handle
{
	function __construct(int $handle)
	{
		parent::__construct($handle);
	}

	function __destruct()
	{
		if($this->isValid())
		{
			Kernel32::CloseHandle($this->handle);
		}
	}
}
