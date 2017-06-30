<?php

class Ebizmarts_BakerlooRestful_Model_Observer_Adminuser {

	public function refreshUsersPermissions($observer) {
		$role = $observer->getEvent()->getObject();

		if( !is_object($role) )
			return $this;

		$roleUsers = $role->getRoleUsers();

		foreach($roleUsers as $_userId) {
			$user = Mage::getModel('admin/user')
				->load($_userId);
            if($user->getId())
				$user->setModified( Mage::getModel('core/date')->gmtDate() )
                    ->save();
		}

	}

	public function savePincode($observer){
        $user = $observer->getEvent()->getObject();

        if(!is_object($user))
            return $this;

        $userId = $user->getId();
        $userPincode = Mage::app()->getRequest()->getParam('pos_pin_code');

        if(isset($userPincode) and $userPincode != '****') {
            try{
                $pincode = Mage::getModel('bakerloo_restful/pincode')->load($userId, 'admin_user_id');

                if(!$pincode->getAdminUserId())
                    $pincode->setAdminUserId($userId);

                if($pincode->getPincode() != $userPincode)
                    $pincode->savePincode($userPincode);
            }
            catch(Mage_Exception $e){
                Mage::getSingleton('core/session')->addError('Couldn\'t save your pin: ' . $e->getMessage());
            }
        }
    }

}