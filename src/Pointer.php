<?php
namespace GtaExternal;

class Pointer
{
	public int $process_id;
	public int $address;

	function __construct(int $process_id, int $address)
	{
		$this->process_id = $process_id;
		$this->address = $address;
	}

	function isNullptr() : bool
	{
		return $this->address == 0;
	}

	function add(int $offset) : Pointer
	{
		return new Pointer($this->process_id, $this->address + $offset);
	}

	function readBinary(int $bytes) : string
	{
		return join("", array_map("hex2bin", str_split(CppInterface::read_bytes($this->process_id, $this->address, $bytes), 2)));
	}

	function read_int32() : int
	{
		return unpack("l", $this->readBinary(4))[1];
	}

	function read_uint32() : int
	{
		return unpack("V", $this->readBinary(4))[1];
	}

	function rip() : Pointer
	{
		return $this->add($this->read_int32())->add(4);
	}

	function read_uint64() : int
	{
		return unpack("Q", $this->readBinary(8))[1];
	}

	function dereference() : Pointer
	{
		return new Pointer($this->process_id, $this->read_uint64());
	}

	function readFloat() : float
	{
		return unpack("f", $this->readBinary(4))[1];
	}

	static function byteToHex(int $b)
	{
		return str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
	}

	function writeByte(int $b) : void
	{
		CppInterface::write_bytes($this->process_id, $this->address, self::byteToHex($b));
	}

	function writeFloat(float $value) : void
	{
		$bin = pack("f", $value);
		$hex = "";
		foreach(str_split($bin) as $c)
		{
			$hex .= self::byteToHex(ord($c));
		}
		CppInterface::write_bytes($this->process_id, $this->address, $hex);
	}
}
