<?php
/**
 * Copyright (c) 2009-2015 Vaimo Norge AS
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Icommerce_Adwords
 * @copyright   Copyright (c) 2009-2015 Vaimo Norge AS
 * @author      Simen Thorsrud <simen.thorsrud@vaimo.com>
 * @author      Branislav Jovanovic <branislav.jovanovic@vaimo.com>
 */

/**
 * Class Icommerce_Adwords_Block_View
 */
class Icommerce_Adwords_Block_View extends Mage_Core_Block_Template
{

    /**
	  * Active or not
	  *
	  * @param   none
	  * @return  bool
	*/
	public function isActive()
	{
		return (bool)Mage::getStoreConfig('adwords/settings/active');
	}

    /**
	  * Returns Google Conversion ID
	  *
	  * @param   none
	  * @return  string
	*/
	public function getGoogleConversionId()
	{
		return (string)Mage::getStoreConfig('adwords/settings/google_conversion_id');
	}

	/**
	  * Returns Google Conversion Language
	  *
	  * @param   none
	  * @return  string
	*/
	public function getGoogleConversionLanguage()
	{
		return (string)Mage::getStoreConfig('adwords/settings/google_conversion_language');
	}

	/**
	  * Returns Google Conversion Format
	  *
	  * @param   none
	  * @return  string
	*/
	public function getGoogleConversionFormat()
	{
		return (string)Mage::getStoreConfig('adwords/settings/google_conversion_format');
	}

	/**
	  * Returns Google Conversion Color
	  *
	  * @param   none
	  * @return  string
	*/
	public function getGoogleConversionColor()
	{
		return (string)Mage::getStoreConfig('adwords/settings/google_conversion_color');
	}

	/**
	  * Returns Google Conversion Label
	  *
	  * @param   none
	  * @return  string
	*/
	public function getGoogleConversionLabel()
	{
		return (string)Mage::getStoreConfig('adwords/settings/google_conversion_label');
	}

	/**
	  * Returns Google Conversion Value
	  *
	  * @param   none
	  * @return  string
	*/
	public function getGoogleConversionValue()
	{
		return (string)Mage::getStoreConfig('adwords/settings/google_conversion_value');
	}

    /**
     * Checks if remarketing scripts should be added
     *
     * @return bool
     */
    public function isGoogleRemarketingActive()
    {
        return Mage::getStoreConfigFlag('adwords/settings/google_remarketing_active');
    }

    /**
     * Is module active or not?
     *
     * @return  bool
     */
    public function getGoogleRemarketingOnly()
    {
        return (bool)Mage::getStoreConfig('adwords/settings/google_remarketing_only');
    }

    /**
     * Gets google_tag_params array with all standard dynamic variables as defined in Google Adwords.
     *
     * Appends to google_tag_params any google_custom_params that are defined in Magento admin
     *
     * @return string Json encoded array of google_tag_params
     */
    public function getGoogleTagParams($vertical = false)
    {
        /** @var Icommerce_Adwords_Model_Remarketing_Vertical_Abstract $remarketingModel */
        $remarketingModel = $this->_getRemarketingModel($vertical);

        /** @var array $params */
        $params = $remarketingModel->getGoogleTagParamsArray();

        /** @var array $customTagParams */
        $customTagParams = $this->getGoogleCustomTagParams();

        $params = array_merge($params, $customTagParams);

        return $this->_getJson($params);
    }

    /**
     * Sanitize and return Json
     *
     * Wrap value strings in single quotes rather than double quotes because
     * it looks prettier with ' rather than \" in json string in template
     *
     * @param array $params
     * @return string Json
     */
    protected function _getJson(array $params)
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        array_walk($params, function (&$value) {
            $value = str_replace('"', "'", $value);
        });

        return $coreHelper->jsonEncode($params);
    }

    /**
     * Get any google_custom_params that are defined in Magento admin
     *
     * @return array
     */
    public function getGoogleCustomTagParams()
    {
        /** @var string $configValue */
        $configValue = Mage::getStoreConfig('adwords/settings/google_tag_params');

        /** @var array $configValueArray    Array of custom google_custom_params parameters
         *                                  that are added in Magento admin */
        $configValueArray = @unserialize($configValue);

        /** @var array $params The return array */
        $params = array();

        /*
        * Add whatever custom static parameters are added via Magento admin panel
        */
        if (!empty($configValueArray)) {
            foreach ($configValueArray as $value) {
                $params[$value['google_tag_param_name']] = $value['google_tag_param_value'];
            }
        }

        return $params;
    }

    /**
     * Use factory to get correct remarketing model
     *
     * @param bool|false $vertical
     * @return Icommerce_Adwords_Model_Remarketing_Vertical_Abstract
     */
    protected function _getRemarketingModel($vertical = false)
    {
        /** @var Icommerce_Adwords_Model_Remarketing_Vertical $factoryModel */
        $factoryModel = Mage::getModel('adwords/remarketing_vertical');

        /** @var Icommerce_Adwords_Model_Remarketing_Vertical_Abstract $remarketingModel */
        $remarketingModel = $factoryModel::factory($vertical);

        return $remarketingModel;
    }
}
