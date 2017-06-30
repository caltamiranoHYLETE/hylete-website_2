<?php

class Ebizmarts_BakerlooRestful_Helper_Acl extends Mage_Core_Helper_Abstract {

    /**
     * Check if given username is allowed to access $resource.
     * If user is not active, it won't be allowed no matter what the role permissions are.
     *
     * @param string $username
     * @param string $resource
     * @return boolean
     */
    public function isAllowed($username, $resource) {

        $allowed = false;

        $user = $this->getUser($username);

        if ( $user->getId() && (1 === (int)$user->getIsActive()) ) {

            $acl = Mage::getResourceModel('admin/acl')->loadAcl();

            if (!preg_match('/^admin/', $resource)) {
                $resource = 'admin/' . $resource;
            }

            try {
                if ($acl->isAllowed($user->getAclRole(), 'all', null)) {
                    $allowed = true;
                }
            } catch (Exception $e) {
                $allowed = false;
            }

            try {
                $allowed = $acl->isAllowed($user->getAclRole(), $resource, null);
            } catch (Exception $e) {
                $allowed = false;
            }

        }

        return $allowed;

    }

    /**
     * Retrieve admin/user from database
     *
     * @param string $username
     * @return Mage_Admin_Model_User
     */
    public function getUser($username) {
        $user = Mage::getModel('admin/user')->loadByUsername($username);

        return $user;
    }
}