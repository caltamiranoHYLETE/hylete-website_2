<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Model_Resource_User
 */
class Effy_Freshdesk_Model_Resource_User extends Varien_Object
{
    public function getFreshdeskModel()
    {
        return Mage::getSingleTon('effy_freshdesk/freshdesk_users');
    }

    public function getIdFieldName()
    {
        return Effy_Freshdesk_Model_Freshdesk_Users::PARAM_ID;
    }

    /**
     * @param Effy_Freshdesk_Model_User $user
     * @param int                            $id
     * @param null                           $field
     *
     * @return array|null
     */
    public function load($user, $id, $field = null)
    {
        return $user->addData(
            $this->getFreshdeskModel()
                ->getUser($id)
        );
    }

    public function save(Effy_Freshdesk_Model_User $user)
    {
        $this->getFreshdeskModel()
            ->setDataFromArray($user->getData())
            ->saveUser();

        return $this;
    }
}