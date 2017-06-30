<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * Class Icommerce_Adwords_Model_Remarketing_Vertical
 */
class Icommerce_Adwords_Model_Remarketing_Vertical
{

    /** The Magento model alias of this class */
    const MODEL_ALIAS_PREFIX = 'adwords/remarketing_vertical';

    /**
     * Return model assigned to output of remarketing tag.
     *
     * Each Google Adwords vertical has its own model and should extend
     * abstract class Icommerce_Adwords_Model_Remarketing_Vertical_Abstract
     *
     * @link https://support.google.com/adwords/answer/3103357?hl=en
     * @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en
     * @see Icommerce_Adwords_Model_Remarketing_Vertical_Abstract
     * @see Icommerce_Adwords_Model_Remarketing_Vertical_Retail
     *
     * @param bool|false $verticalCode
     * @return false|Icommerce_Adwords_Model_Remarketing_Vertical_Abstract
     */
    public static function factory($verticalCode = false)
    {
        /** @var false $object */
        $object = false;

        /** @var Icommerce_Adwords_Helper_Config $configHelper */
        $configHelper = Mage::helper('adwords/config');

        /*
         * If $verticalCode is not defined then we fall back to whatever
         * vertical is configured in Magento admin. This is the
         * most common use scenario
         */
        if (!$verticalCode) {
            $verticalCode = $configHelper->getCurrentVertical();
        }

        /** @var string $verticalCode */
        $verticalCode = strtolower($verticalCode);

        /** @var string $modelAlias Magento model alias */
        $modelAlias = self::MODEL_ALIAS_PREFIX . '_' . $verticalCode;

        try {
            /** @var Icommerce_Adwords_Model_Remarketing_Vertical_Abstract $object */
            $object = Mage::getModel($modelAlias, array($verticalCode));

            if (!($object instanceof Icommerce_Adwords_Model_Remarketing_Vertical_Abstract)) {

                /** @var string $message Exception message */
                $message = 'You have not implemented a model for the Google Adwords vertical "'
                    . $verticalCode . '"';

                Mage::throwException($message);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $object;
    }
}
