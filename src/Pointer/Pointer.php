<?php
namespace V\Pointer;
use V\
{NativeHelper, Handle};
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
		return NativeHelper::$buffer_address_start <= $this->address && $this->address + $min_bytes <= NativeHelper::$buffer_address_end;
	}

	function buffer(int $bytes) : void
	{
		NativeHelper::process_read_bytes($this->handle, $this->address, $bytes);
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
		$i = $this->address - NativeHelper::$buffer_address_start;
		$end = $i + $bytes;
		for(; $i < $end; $i++)
		{
			$bin_str .= chr(NativeHelper::buffer_read_byte($i));
		}
		return $bin_str;
	}

	function readByte() : int
	{
		$this->ensureBuffer(1);
		return NativeHelper::buffer_read_byte($this->address - NativeHelper::$buffer_address_start);
	}

	function readInt32() : int
	{
		return unpack("l", $this->readBinary(4))[1];
	}

	function readUInt32() : int
	{
		return unpack("V", $this->readBinary(4))[1];
	}

	function rip() : Pointer
	{
		return $this->add($this->readInt32())->add(4);
	}

	function readUInt64() : int
	{
		return unpack("Q", $this->readBinary(8))[1];
	}

	function dereference() : Pointer
	{
		return new Pointer($this->handle, $this->readUInt64());
	}

	function readString() : string
	{
		$len = 0;
		while($this->add($len)->readByte() != 0)
		{
			$len++;
		}
		return $this->readBinary($len);
	}

	function readFloat() : float
	{
		return unpack("f", $this->readBinary(4))[1];
	}

	function writeByte(int $b) : void
	{
		NativeHelper::buffer_write_byte(0, $b);
		NativeHelper::process_write_bytes($this->handle, $this->address, 1);
	}

	function writeBinary(string $bin) : void
	{
		$i = 0;
		foreach(str_split($bin) as $c)
		{
			NativeHelper::buffer_write_byte($i++, ord($c));
		}
		NativeHelper::process_write_bytes($this->handle, $this->address, $i);
	}

	function writeInt32(int $value) : void
	{
		$this->writeBinary(pack("l", $value));
	}

	function writeFloat(float $value) : void
	{
		$this->writeBinary(pack("f", $value));
	}
}
