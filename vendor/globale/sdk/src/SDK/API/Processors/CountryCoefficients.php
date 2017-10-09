<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class CountryCoefficients extends API\Processor {

	/**
	 * API path Browsing/CountryCoefficients
	 * @var string
	 * @access protected
	 */
	protected $Path = 'Browsing/CountryCoefficients';

	/**
	 * Name of the common class API should return.
	 * @var string
	 */
	protected $ObjectResponse = "CountryCoefficient";

	/**
	 * CountryCoefficients constructor.
	 * @param Common\ApiParams $Params
	 */
	public function __construct(Common\ApiParams $Params) {

		$this->setParams($Params);
	}

}