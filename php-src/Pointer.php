<?php
namespace GtaExternal;

require_once __DIR__."/CppInterface.php";

class Pointer
{
	public $process;
	public $address;

	function __construct(string $process, int $address)
	{
		$this->process = $process;
		$this->address = $address;
	}

	function isNullptr() : bool
	{
		return $this->address == 0;
	}

	function add(int $offset) : Pointer
	{
		return new Pointer($this->process, $this->address + $offset);
	}

	function readBinary(int $bytes) : string
	{
		return join("", array_map("hex2bin", str_split(CppInterface::readBytes($this->process, $this->address, $bytes), 2)));
	}

	function readLong() : int
	{
		return unpack("Q", $this->readBinary(8))[1];
	}

	function dereference() : Pointer
	{
		return new Pointer($this->process, $this->readLong());
	}

	function readFloat() : float
	{
		return unpack("f", $this->readBinary(4))[1];
	}

	function writeByte(int $b) : void
	{
		CppInterface::writeBytes($this->process, $this->address, str_pad(dechex($b), 2, "0", STR_PAD_LEFT));
	}

	function writeFloat(float $value) : void
	{
		CppInterface::writeBytes($this->process, $this->address, bin2hex(pack("f", $value)));
	}
}
