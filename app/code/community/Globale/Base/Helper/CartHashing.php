<?php

/**
 * Class that deal with CartID hashing actions (if exists)
 * For browsing lite will use encrypted string of CartID, and not Magento CartId, for Outside usage (to GEM ) by security reasons.
 * Class Globale_Base_Helper_CartHashing
 */
class Globale_Base_Helper_CartHashing extends Mage_Core_Helper_Abstract {


	/**
	 * Generate cart hash using salt
	 * @param string $CartId
	 * @param string $Salt
	 * @return string
	 */
	public function generateCartIdentifier($CartId,$Salt){
		$CartIdentifier = $CartId.'_'.md5(hash('sha512',$CartId.$Salt,true));
		return $CartIdentifier;
	}

	/**
	 * Extract cart id from the hash cart identifier
	 * @param string $CartIdentifier
	 * @param string $Salt
	 * @return string
	 */
	public function fetchCartId($CartIdentifier,$Salt){
		$CartId = null;

		if(strpos($CartIdentifier,'_') !== false){
			$CartIdentifierArray = explode('_',$CartIdentifier);
			$UnverifiedCartId = $CartIdentifierArray[0];
			if($CartIdentifier == $this->generateCartIdentifier($UnverifiedCartId,$Salt)){
				$CartId = $UnverifiedCartId;
			}
		}
		return $CartId;
	}
}