<?php

namespace GlobalE\SDK\Models\Common\Response;

use GlobalE\SDK\Models\Common;

class GetCartError extends Common  {

	/**
	 * @var boolean
	 */
	public $success = false;

	/**
	 * @var string
	 */
	public $errorMessage;


	/**
	 * @return bool
	 */
	public function isSuccess()
	{
		return $this->success;
	}

	/**
	 * @param bool $success
	 * @return GetCartError
	 */
	public function setSuccess($success)
	{
		$this->success = $success;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}

	/**
	 * @param string $errorMessage
	 * @return GetCartError
	 */
	public function setErrorMessage($errorMessage)
	{
		$this->errorMessage = $errorMessage;
		return $this;
	}



}