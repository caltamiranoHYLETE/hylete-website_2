<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Block_Adminhtml_Page_Type_Selector extends Vaimo_Cms_Block_Html_Select
{
    protected function _construct()
    {
        $type = $this->_factory->getModel('vaimo_cms/page_type');

        foreach ($type->getAllTypes() as $code => $label) {
            $this->addOption($code, $this->__($label));
        }

        $this->setId('vcms-page-type');

        $category = Mage::registry('current_category');

        if ($this->_isRootCategory($category)) {
            $this->setExtraParams('disabled=disabled');
        }
    }

    protected function _isRootCategory($category)
    {
        return $category && $category->hasLevel() && $category->getLevel() <= 1;
    }

    public function getValue()
    {
        $category = Mage::registry('current_category');

        if ($this->_isRootCategory($category)) {
            return Vaimo_Cms_Model_Page_Type::TYPE_CMS;
        }

        return $category ? $category->getData(Vaimo_Cms_Helper_Data::PAGE_TYPE_ATTRIBUTE_CODE) : null;
    }
}