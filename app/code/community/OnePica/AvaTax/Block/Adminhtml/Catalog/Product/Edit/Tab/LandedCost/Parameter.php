<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Product Avatax tab
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Block_Adminhtml_Catalog_Product_Edit_Tab_LandedCost_Parameter
    extends OnePica_AvaTax_Block_Adminhtml_Catalog_Product_Edit_Tab_LandedCost_Abstract
{
    /**
     * Initialize block
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->setTemplate('onepica/avatax/product/edit/tab/renderer/parameter.phtml');
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /** @var \Mage_Adminhtml_Block_Widget_Button $button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                       ->setData(
                           array(
                               'label'   => Mage::helper('catalog')->__('Add Parameter'),
                               'onclick' => 'return lcUnitControl.addItem()',
                               'class'   => 'add'
                           )
                       );
        $button->setName('add_lcost_unit_item_button');

        $this->setChild('add_button', $button);

        return parent::_prepareLayout();
    }

    /**
     * @var OnePica_AvaTax_Model_Records_Mysql4_Parameter_Collection|null;
     */
    protected $parameters = null;

    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;

    /**
     * Prepare group price values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array();
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }

        return $values;
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        return $data;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int|null $groupId return name by customer group id
     * @return array|string
     */
    public function getParameters()
    {
        if (empty($this->parameters)) {
            /* @var OnePica_AvaTax_Model_Records_Mysql4_Parameter_Collection $collection */
            $this->parameters = Mage::getModel('avatax_records/parameter')->getCollection();
            $this->parameters->load();
        }

        return $this->parameters;
    }

    /**
     * Retrieve number of websites
     *
     * @return int
     */
    public function getWebsiteCount()
    {
        return count($this->getWebsites());
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    public function isMultiWebsites()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */
    public function getWebsites()
    {
        if (!is_null($this->_websites)) {
            return $this->_websites;
        }

        $this->_websites = array(
            0 => array(
                'name'     => Mage::helper('catalog')->__('All Websites'),
                'currency' => Mage::app()->getBaseCurrencyCode()
            )
        );

        if (!$this->isScopeGlobal() && $this->getProduct()->getStoreId()) {
            /** @var $website Mage_Core_Model_Website */
            $website = Mage::app()->getStore($this->getProduct()->getStoreId())->getWebsite();

            $this->_websites[$website->getId()] = array(
                'name'     => $website->getName(),
                'currency' => $website->getBaseCurrencyCode()
            );
        } elseif (!$this->isScopeGlobal()) {
            $websites = Mage::app()->getWebsites(false);
            $productWebsiteIds = $this->getProduct()->getWebsiteIds();
            foreach ($websites as $website) {
                /** @var $website Mage_Core_Model_Website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $this->_websites[$website->getId()] = array(
                    'name'     => $website->getName(),
                    'currency' => $website->getBaseCurrencyCode()
                );
            }
        }

        return $this->_websites;
    }

    /**
     * @param \OnePica_AvaTax_Model_Records_Parameter $unit
     * @return string
     */
    public function getDataType($unit)
    {
        return $unit->getAvalaraParameterType() === 'Mass' ? 'number' : 'text';
    }

    /**
     * Retrieve default value for unit of measurement
     *
     * @return int
     */
    public function getDefaultUnitOfMeasurement()
    {
        return -1;
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return Mage::app()->getStore($this->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Retrieve customized price column header
     *
     * @param string $default
     * @return string
     */
    public function getPriceColumnHeader($default)
    {
        if ($this->hasData('price_column_header')) {
            return $this->getData('price_column_header');
        } else {
            return $default;
        }
    }

    /**
     * Retrieve Group Price entity attribute
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Check group price attribute scope is global
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getAttribute()->isScopeGlobal();
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    public function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || Mage::app()->isSingleStoreMode()) {
            return false;
        }

        return true;
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    public function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->getProduct()->getStoreId()) {
            return false;
        }

        return true;
    }
}
