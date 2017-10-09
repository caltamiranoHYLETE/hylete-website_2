<?php
namespace GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\Models\Common;


/**
 * Class AppVersionWebStoreDetails
 *
 * @method getWebStoreCode()
 * @method getWebStoreInstanceCode()
 *
 * @method $this setWebStoreCode($WebStoreCode)
 * @method $this setWebStoreInstanceCode($WebStoreInstanceCode)
 *
 * @package GlobalE\SDK\Models\Common\Request
 */
class AppVersionWebStoreDetails extends Common {

	/**
	 * Code used on the merchant’s side to identify the web store where the current cart is originating from.
	 * This code should be used in case of multi-store setup on the merchant’s site.
	 * @var string
	 */
	public $WebStoreCode;


	/**
	 * @var string
	 */
	public $WebStoreInstanceCode;

}