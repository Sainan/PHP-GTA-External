<?php
namespace V;
use RuntimeException;
use Throwable;
class Kernel32Exception extends RuntimeException
{
	function __construct(string $message)
	{
		$code = Kernel32::GetLastError();
		parent::__construct($message.": Error Code $code", $code, null);
	}
}
