<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @category    Icommerce
 * @package     Icommerce_CatalogImageFormat
 * @copyright   Copyright (c) 2009-2012 Icommerce Nordic AB
 * @author      Wilko Nienhaus
 */

class Icommerce_CatalogImageFormat_Model_Observer
{
    const HELPER_CLASS = 'Icommerce_CatalogImageFormat_Helper_Image';

    public function setHelperRewrite(Varien_Event_Observer $observer)
    {
        $node = Mage::getConfig()->getNode('global/helpers/catalog/rewrite/image');
        $currentValue = $node ? (string)$node : '';

        if (Mage::getStoreConfig('catalogimageformat/settings/rewrite')) {
            if ($currentValue != self::HELPER_CLASS) {
                Mage::getConfig()->setNode('global/helpers/catalog/rewrite/image',self::HELPER_CLASS);
            }
        } else {
            if ($currentValue == self::HELPER_CLASS) {
                $xml = Mage::getConfig()->getNode();
                unset($xml->global->helpers->catalog->rewrite->image);
            }
        }
    }
}
