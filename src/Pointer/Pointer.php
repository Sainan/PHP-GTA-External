<?php
namespace GtaExternal\Pointer;
use GtaExternal\
{CppInterface, Handle};
class Pointer
{
	public Handle $handle;
	public int $address;

	function __construct(Handle $handle, int $address)
	{
		$this->handle = $handle;
		$this->address = $address;
	}

	function __toString() : string
	{
		return dechex($this->address);
	}

	function isNullptr() : bool
	{
		return $this->address == 0;
	}

	function add(int $offset) : Pointer
	{
		return new Pointer($this->handle, $this->address + $offset);
	}

	function subtract(int $offset) : Pointer
	{
		return new Pointer($this->handle, $this->address - $offset);
	}

	function isBuffered(int $min_bytes = 1) : bool
	{
		return CppInterface::$buffer_address_start <= $this->address && $this->address + $min_bytes <= CppInterface::$buffer_address_end;
	}

	function buffer(int $bytes) : void
	{
		CppInterface::process_read_bytes($this->handle, $this->address, $bytes);
	}

	function ensureBuffer(int $bytes) : void
	{
		if(!$this->isBuffered($bytes))
		{
			$this->buffer($bytes);
		}
	}

	function readBinary(int $bytes) : string
	{
		$this->ensureBuffer($bytes);
		$bin_str = "";
		$i = $this->address - CppInterface::$buffer_address_start;
		$end = $i + $bytes;
		for(; $i < $end; $i++)
		{
			$bin_str .= chr(CppInterface::buffer_read_byte($i));
		}
		return $bin_str;
	}

	function readByte() : int
	{
		$this->ensureBuffer(1);
		return CppInterface::buffer_read_byte($this->address - CppInterface::$buffer_address_start);
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
		return new Pointer($this->handle, $this->read_uint64());
	}

	function readFloat() : float
	{
		return unpack("f", $this->readBinary(4))[1];
	}

	function writeByte(int $b) : void
	{
		CppInterface::buffer_write_byte(0, $b);
		CppInterface::process_write_bytes($this->handle, $this->address, 1);
	}

	function writeFloat(float $value) : void
	{
		$i = 0;
		foreach(str_split(pack("f", $value)) as $c)
		{
			CppInterface::buffer_write_byte($i++, ord($c));
		}
		CppInterface::process_write_bytes($this->handle, $this->address, 4);
	}
}
