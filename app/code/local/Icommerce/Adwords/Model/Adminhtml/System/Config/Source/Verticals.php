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
 */

/**
 * Class Icommerce_Adwords_Model_Adminhtml_System_Config_Source_Verticals
 */
class Icommerce_Adwords_Model_Adminhtml_System_Config_Source_Verticals
{

    /**
     * Get options array for Magento admin
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Icommerce_Adwords_Helper_Config $configHelper */
        $configHelper = Mage::helper('adwords/config');

        /** @var array $verticalsFromConfig */
        $verticalsFromConfig = $configHelper->getVerticals();

        /** @var array $optionArray */
        $optionArray = array();

        foreach ($verticalsFromConfig as $code => $verticalInfo) {

            /** @var false|Icommerce_Adwords_Model_Remarketing_Vertical_Abstract $model */
            $model = Icommerce_Adwords_Model_Remarketing_Vertical::factory($code);

            if (is_object($model)) {

                /** @var string $label */
                $label = $model->getLabel();

                $optionArray[] = array(
                    'value' => $code,
                    'label' => $label,
                );
            }

        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        /** @var array $returnArray */
        $returnArray = array();

        /** @var array $optionArray */
        $optionArray = $this->toOptionArray();

        foreach ($optionArray as $verticalOption) {
            $returnArray[$verticalOption['value']] = $verticalOption['label'];
        }

        return $returnArray;
    }

}
