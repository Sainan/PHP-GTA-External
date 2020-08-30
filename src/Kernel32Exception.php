<?php
namespace V;
use RuntimeException;
use Throwable;
class Kernel32Exception extends RuntimeException
{
	function __construct()
	{
		$code = Kernel32::GetLastError();
		parent::__construct("Error Code $code", $code, null);
	}
}
