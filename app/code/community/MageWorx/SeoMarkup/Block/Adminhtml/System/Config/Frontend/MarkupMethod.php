<?php
/**
 * MageWorx
 * MageWorx SeoExtended Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoExtended
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Block_Adminhtml_System_Config_Frontend_MarkupMethod extends MageWorx_SeoAll_Block_Adminhtml_Config_Frontend_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var MageWorx_SeoMarkup_Helper_Config $configHelper */
        $configHelper = Mage::helper('mageworx_seomarkup/config');

        if (!$configHelper->isSimpleHtmlDomAvailable()) {
            $element->setComment(
                "<font color = 'orange'>" . $this->__('"HTML Injection" method is not available now.') . "<br>" .
                $this->__(
                    "You should download the %s file from %s and put it to your magento host into the %s directory.",
                    '"simple_html_dom.php"',
                    '"https://sourceforge.net/projects/simplehtmldom/"',
                    '"lib/htmlparser"'
                ) . "</font><br>" .
                $element->getComment()
            );
        }

        return parent::render($element);
    }
}