<?php
/**
 * Class Mediotype_OffersTab_Helper_Data
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_TITLE = "Today's Offers";

    private $_hideOnUrls;

    /**
     * Mediotype_OffersTab_Helper_Data constructor.
     */
    public function __construct()
    {
        $this->_hideOnUrls = $this->getOffersTabBlackList();
    }

    /**
     * Responsible for returning a list of CMS static block ids to display
     */
    public function getFilteredOffers()
    {
        $filterCategory = $this->_getCurrentCategory();
        $filterProduct = $this->_getCurrentProduct();
        $filterCustomerGroup = $this->_getCurrentCustomerGroup();

        $model = Mage::getModel('mediotype_offerstab/offer');
        $collection = $model->getCollection();
        $collection->setOrder('priority', 'DESC');
        $collection->addFieldToFilter('status', 1);
        $collection->load();

        $offers = array();

        foreach ($collection->getItems() as $offer) {
            $categoryMatch = false;
            $productMatch = false;
            $customerGroupMatch = false;

            $offerCategories = $offer->getCategoryIds();
            $offerProducts = $offer->getProductIds();
            $offerCustomerGroups = $offer->getCustomerGroupIds();

            // Check categories
            if ($offerCategories == NULL) {
                $categoryMatch = true;

            } else {
                $categories = explode(",", $offerCategories);

                if ($filterCategory != null && array_contains($categories, $filterCategory->getId())) {
                    $categoryMatch = true;
                }
            }

            // Check products
            if ($offerProducts == NULL) {
                $productMatch = true;

            } else {
                $products = explode(",", $offerProducts);

                if ($filterProduct != null && array_contains($products, $filterProduct->getId())) {
                    $productMatch = true;
                }
            }

            // Check customer group
            if (empty($offerCustomerGroups)) {
                $customerGroupMatch = true;
            } else {
                $filterCustomerGroup = (string) $filterCustomerGroup;

                if (array_contains($offerCustomerGroups, $filterCustomerGroup)) {
                    $customerGroupMatch = true;
                }
            }
            // Add to list if applicable
            if ($categoryMatch && $productMatch && $customerGroupMatch) {
                $offers[] = $offer;
            }
        }

        return $offers;
    }

    /**
     * @return mixed
     */
    protected function _getCurrentCategory()
    {
        return Mage::registry('current_category');
    }

    /**
     * @return mixed
     */
    protected function _getCurrentProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * @return mixed
     */
    protected function _getCurrentCustomerGroup()
    {
        return Mage::getSingleton('customer/session')->getCustomerGroupId();
    }

    /**
     * Place holder until the adminhtml configuration pages
     * @param integer $groupId The customer group ID.
     * @return string
     */
    public function getTitle($groupId = null)
    {
        if (is_null($groupId)) {
            $groupId = $this->_getCurrentCustomerGroup();
        }

        $helper = Mage::helper('core/unserializeArray');

        try {
            $config = $helper->unserialize(
                Mage::getStoreConfig('mediotype_offerstab/general/offers_tab_title')
            );

            foreach ($config as $item) {
                if ((int) $item['group_id'] === (int) $groupId) {
                    $result = $item['title'];
                    break;
                }
            }
        } catch (Exception $error) {
            $result = null;
        }

        return $result ?: self::DEFAULT_TITLE;
    }

    /**
     * Retrieve the blacklist system configuration and return it as an array
     * @return array
     */
    public function getOffersTabBlackList()
    {
        $_blackList = str_replace(' ', '', Mage::getStoreConfig('mediotype_offerstab/general/black_list'));
        return (!is_null($_blackList) ? explode(',', $_blackList) : []);
    }

    /**
     * @param $url
     * @return bool
     */
    public function shouldShowForUrl($url)
    {
        return !array_contains($this->_hideOnUrls, $url);
    }
}
