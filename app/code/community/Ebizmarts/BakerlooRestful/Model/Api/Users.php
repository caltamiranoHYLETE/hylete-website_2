<?php

class Ebizmarts_BakerlooRestful_Model_Api_Users extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    public $defaultSort   = "modified";
    public $pageSize      = 200;
    protected $_model     = "admin/user";
    private $_permissions = array();

    protected function _getIndexId() {
        return 'user_id';
    }

    /**
     * Validate provided credentials
     *
     * PUT
     */
    public function put() {

        $data = $this->getJsonPayload();

        /** @var $user Mage_Admin_Model_User */
        $user = $this->_authenticate($data->username, $data->password);

        $return = array();

        if($user->getId()) {
            $return = $this->_createDataObject(null, $user);
        }
        else {
            Mage::throwException('Invalid login.');
        }

        return $return;
    }

    private function _authenticate($username, $password) {
        $config = Mage::getStoreConfigFlag('admin/security/use_case_sensitive_login');

        try {

            $user      = Mage::getModel('admin/user')->loadByUsername($username);
            $sensitive = ($config) ? $username == $user->getUsername() : true;

            if ($sensitive && $user->getId() && Mage::helper('core')->validateHash($password, $user->getPassword())) {
                if ($user->getIsActive() != '1') {
                    $user = new Varien_Object;
                    Mage::throwException('This account is inactive.');
                }
                if (!$user->hasAssigned2Role($user->getId())) {
                    $user = new Varien_Object;
                    Mage::throwException('Access denied.');
                }
            }
            else {
                $user = new Varien_Object;
            }

        }
        catch (Mage_Core_Exception $e) {
            $user = new Varien_Object;
            throw $e;
        }

        return $user;
    }

    public function _createDataObject($id = null, $data = null) {

        if(is_null($data)) {
            $user = Mage::getModel($this->_model)->load($id);
        }
        else {
            $user = $data;
        }

        if(is_null($user->getCreated()) or is_null($user->getModified())) {
            $user->save();
        }

        $result = array(
            'user_id'     => (int) $user->getId(),
            'firstname'   => $user->getFirstname(),
            'lastname'    => $user->getLastname(),
            'email'       => $user->getEmail(),
            'username'    => $user->getUsername(),
            'created'     => $user->getCreated(),
            'modified'    => $user->getModified(),
            'is_active'   => (int) $user->getIsActive(),
            'permissions' => $this->_getPermissions($user->getRole()->getId()),
            'pin_code'    => (string)Mage::getModel('bakerloo_restful/pincode')->load($user->getId(), 'admin_user_id')->getPincode(),
            );

        return $result;

    }

    public function _getPermissions($roleId) {

        if( !array_key_exists($roleId, $this->_permissions) ) {

            $resources = Mage::getModel('admin/roles')->getResourcesList();

            $rules_set = Mage::getResourceModel('admin/rules_collection')
                            ->getByRoles($roleId)
                            ->load();
            $acl = array();
            foreach ($rules_set->getItems() as $item) {
                $itemResourceId = $item->getResourceId();

                if('all' == $itemResourceId) {
                    $acl ['all']= $item->getPermission();
                    continue;
                }

                if (!preg_match('/^admin\/bakerloo_api/', $itemResourceId)) {
                    continue;
                }

                if($itemResourceId == 'admin/bakerloo_api') {
                    continue;
                }

                if (array_key_exists(strtolower($itemResourceId), $resources)) {
                    $acl [ str_replace('admin/bakerloo_api/', '', $itemResourceId) ]= $item->getPermission();
                }
            }

            if(empty($acl)) {
                $acl = new stdClass;
            }

            $this->_permissions[$roleId] = $acl;
        }

        return $this->_permissions[$roleId];
    }

}