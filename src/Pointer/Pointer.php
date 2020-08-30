<?php
namespace V\Pointer;
use FFI;
use FFI\CData;
use V\
{Handle\ProcessHandle, Kernel32};
class Pointer
{
	const nullptr = 0;

	public ProcessHandle $processHandle;
	public int $address;

	const BUFFER_SIZE = 0x10000;
	public static CData $buffer;
	private static int $buffer_address_start = 0;
	private static int $buffer_address_end = 0;

	function __construct(ProcessHandle $processProcess, int $address)
	{
		$this->processHandle = $processProcess;
		$this->address = $address;
	}

	function __toString() : string
	{
		return dechex($this->address);
	}

	function isNullptr() : bool
	{
		return $this->address == Pointer::nullptr;
	}

	function add(int $offset) : Pointer
	{
		return new Pointer($this->processHandle, $this->address + $offset);
	}

	function subtract(int $offset) : Pointer
	{
		return new Pointer($this->processHandle, $this->address - $offset);
	}

	function isBuffered(int $min_bytes = 1) : bool
	{
		return self::$buffer_address_start <= $this->address && $this->address + $min_bytes <= self::$buffer_address_end;
	}

	function buffer(int $bytes) : void
	{
		Kernel32::ReadProcessMemory($this->processHandle, $this->address, self::$buffer, $bytes);
		self::$buffer_address_start = $this->address;
		self::$buffer_address_end = $this->address + $bytes;
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
		$i = $this->address - self::$buffer_address_start;
		$end = $i + $bytes;
		for(; $i < $end; $i++)
		{
			$bin_str .= chr(self::$buffer[$i]);
		}
		return $bin_str;
	}

	function readByte() : int
	{
		$this->ensureBuffer(1);
		return self::$buffer[$this->address - self::$buffer_address_start];
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
		return new Pointer($this->processHandle, $this->readUInt64());
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
		self::$buffer[0] = $b;
		self::$buffer_address_start++;
		Kernel32::WriteProcessMemory($this->processHandle, $this->address, self::$buffer, 1);
	}

	function writeString(string $bin) : void
	{
		$i = 0;
		foreach(str_split($bin) as $c)
		{
			self::$buffer[$i++] = ord($c);
		}
		self::$buffer_address_start += $i;
		Kernel32::WriteProcessMemory($this->processHandle, $this->address, self::$buffer, $i);
	}

	function writeInt32(int $value) : void
	{
		$this->writeString(pack("l", $value));
	}

	function writeFloat(float $value) : void
	{
		$this->writeString(pack("f", $value));
	}
}

Pointer::$buffer = FFI::new(FFI::arrayType(FFI::type("uint8_t"), [Pointer::BUFFER_SIZE]));
