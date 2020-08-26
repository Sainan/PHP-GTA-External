<?php /** @noinspection PhpUndefinedMethodInspection */
namespace GtaExternal;

require_once __DIR__."/cpp_api.php";

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
		global $cpp_api;
		return join("", array_map("hex2bin", str_split($cpp_api->read_bytes($this->process_id, $this->address, $bytes), 2)));
	}

	function readLong() : int
	{
		return unpack("Q", $this->readBinary(8))[1];
	}

	function dereference() : Pointer
	{
		return new Pointer($this->process_id, $this->readLong());
	}

	function readFloat() : float
	{
		return unpack("f", $this->readBinary(4))[1];
	}

	function writeByte(int $b) : void
	{
		global $cpp_api;
		$cpp_api->write_bytes($this->process_id, $this->address, str_pad(dechex($b), 2, "0", STR_PAD_LEFT));
	}

	function writeFloat(float $value) : void
	{
		global $cpp_api;
		$cpp_api->write_bytes($this->process_id, $this->address, bin2hex(pack("f", $value)));
	}
}
