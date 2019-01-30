<?php

/**
 * Class Collection
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Model_Resource_Offer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('mediotype_offerstab/offer');
    }

    /**
     * Attempt to load an offer by its assigned code. Returns first match.
     *
     * @param $code
     * @return Mediotype_OffersTab_Model_Offer
     */
    public function getOfferByCode($code)
    {
        $offers = $this->setPageSize(1)
            ->addFieldToFilter(
                'landing_page_url',
                ['like' => "%couponCode={$code}%"]
            );

        return $offers->getFirstItem();
    }
}
