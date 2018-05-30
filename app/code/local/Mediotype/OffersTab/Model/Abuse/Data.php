<?php

/**
 * Abuse requester data model.
 *
 * @category Class
 * @package Mediotype_OffersTab
 * @author Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

class Mediotype_OffersTab_Model_Abuse_Data extends Varien_Object
{
    /**
     * Increment the request try count.
     *
     * @return Mediotype_OffersTab_Model_Abuse_Data
     * @throws Exception
     */
    public function incrementAttempt()
    {
        $this->setAttempts(
            (int) $this->getAttempts() + 1
        )->setLastAttempt($this->getManager()->getTimestamp());

        $this->getManager()->persist($this);

        return $this;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return parent::toArray(
            array('id', 'ip_address', 'attempts', 'last_attempt')
        );
    }

    /**
     * Reset the request try data.
     *
     * @return Mediotype_OffersTab_Model_Abuse_Data
     * @throws Exception
     */
    public function resetAttempts()
    {
        $this->setAttempts(0)
            ->setLastAttempt(null);

        $this->getManager()->persist($this);

        return $this;
    }

    /**
     * Get the request abuse manager instance.
     *
     * @return Mediotype_OffersTab_Model_Abuse
     */
    private function getManager()
    {
        if (!$this->hasData('manager')) {
            $this->setData('manager', Mage::getSingleton('mediotype_offerstab/abuse'));
        }

        return $this->getData('manager');
    }
}
