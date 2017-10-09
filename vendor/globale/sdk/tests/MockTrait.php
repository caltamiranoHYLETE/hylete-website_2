<?php

namespace GlobalE\Test;

trait MockTrait {

	/**
	 * @var array
	 */
	protected $MethodReturns =[];
	/**
	 * @param string $function
	 * @param $return
	 */
	public function setMethodReturn($function, $return)
	{
		$this->MethodReturns[$function] = $return;
	}
	/**
	 * @param array $MethodReturns
	 */
	public function setMethodReturns(array $MethodReturns) {
		$this->MethodReturns = $MethodReturns;
	}
	/**
	 * @param string $function
	 * @return bool True if return exists for $method
	 */
	public function isMethodReturnExist($function) {
		return array_key_exists($function, $this->MethodReturns);
	}

	/**
	 * @param string $function
	 * @return mixed
	 */
	public function methodReturn($function) {
		return $this->MethodReturns[$function];
	}

}