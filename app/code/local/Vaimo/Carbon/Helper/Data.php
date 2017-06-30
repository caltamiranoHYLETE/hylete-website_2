<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Carbon
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @comment     Complementary module to theme_carbon
 */

class Vaimo_Carbon_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XPATH_CONFIG_FOOTER_DISCLAIMER = 'design/footer/disclaimer';
    const XPATH_CONFIG_FOOTER_HOME_URL = 'design/footer/home_url';

    protected $_supportedFonts = array(
        'awesome' => array('label' => 'Font Awesome', 'path' => 'css/font-icons/font-awesome.min.css'),
        'glyph' => array('label' => 'Glyphicons', 'path' => 'css/font-icons/glyphicons.css')
    );

    public function getIconFonts()
    {
        return $this->_supportedFonts;
    }

    public function getFooterDisclaimer($markedWordsWrapper)
    {
        $url = Mage::getStoreConfig(self::XPATH_CONFIG_FOOTER_HOME_URL);
        $text = Mage::getStoreConfig(self::XPATH_CONFIG_FOOTER_DISCLAIMER);

        $markedWordsWrapper = Mage::helper('carbon/text')->replaceVariables($markedWordsWrapper, array(
            'link' => $url,
            'extra' => Mage::helper("core/url")->getCurrentUrl() != Mage::getBaseUrl() ? 'nofollow' : ''
        ));

        return Mage::helper('carbon/text')->wrapMarkedValuesInText($text, array('*' => $markedWordsWrapper));
    }

    public function getProductOptionsClassName($product)
    {
        $className = '';
        $options = $product->getOptions();

        if (!empty($options)) {
            $className = ' custom-options';
        }

        return $className;
    }

    public function getControllerName()
    {
        return Mage::app()->getRequest()->getControllerName();
    }

    public function isResponsiveEnabled()
    {
        return Mage::getStoreConfigFlag('carbon/settings/enable_responsive');
    }

    public function isResponsiveDisabled()
    {
        if (!Mage::getStoreConfigFlag('carbon/settings/enable_responsive')) {
            return 'css/responsive/grid/non-responsive.css';
        }

        return null;
    }

    public function getIconFont()
    {
        $selectedFont = Mage::getStoreConfig('carbon/settings/icons');

        if (isset($this->_supportedFonts[$selectedFont]['path'])) {
            return $this->_supportedFonts[$selectedFont]['path'];
        }

        return false;
    }

    public function isEnterprise()
    {
        return Mage::getStoreConfigFlag('carbon/settings/is_enterprise');
    }

    public function getMetaAuthor()
    {
        $value = Mage::getStoreConfig('design/head/meta_author');

        if (!empty($value)) {
            return $value;
        }

        return false;
    }
}