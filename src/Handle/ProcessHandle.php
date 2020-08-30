<?php
namespace V\Handle;
class ProcessHandle extends ObjectHandle
{
	public int $process_id;
	public int $access;

	function __construct(int $process_id, int $handle, int $access)
	{
		$this->process_id = $process_id;
		$this->access = $access;
		parent::__construct($handle);
	}
}
