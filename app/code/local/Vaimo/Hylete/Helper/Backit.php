<?php

/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */
class Vaimo_Hylete_Helper_Backit extends Mage_Core_Helper_Data
{
    const GOAL = 'back_it_goal';
    const END = 'back_it_end_datetime';
    const ORIGINAL = 'back_it_original_stock';
    const SHIP_DATE = 'ship_date_text';

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /**
     * @return bool
     */
    public function isBackItProduct()
    {
        if ($this->isPreOrderProduct()) {
            return false;
        }

        return $this->getProduct()->getData(self::GOAL) > 0;
    }

    /**
     * @return bool
     */
    public function isPreOrderProduct()
    {
        return (bool) $this->getProduct()->getData(self::SHIP_DATE);
    }

    /**
     * @return int
     */
    public function getBackingPercentage()
    {
        $product = $this->getProduct();
        $goal = $product->getData(self::GOAL);
        if (!$goal) {
            return 0;
        }
        $backed = 0;
        /** @var Mage_Catalog_Model_Product $childProduct */
        foreach ($this->getConfigurableChildren($product) as $childProduct) {
            $stock = $childProduct->getStockItem()->getQty();
            $originalStock = $childProduct->getData(self::ORIGINAL);
            //To minimize risk for errors we just ignore cases where $original stock is "off"
            //TODO a failsafe here could be to just save the current stock as the original stock if it is missing
            // Will also help with setup since Hylete effectivly can ignore this attribute and it will be setup automatically
            if ($originalStock > $stock) {
                $backed += ($originalStock - $stock);
            }
        }

        return intval(round(100 * $backed / $goal));
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return object
     */
    protected function getConfigurableChildren($product)
    {
        $configurableProduct = Mage::getModel('catalog/product_type_configurable')
            ->setProduct($product);

        return $configurableProduct->getUsedProductCollection()
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();
    }

    /**
     * Return the number of days left on the campaign.
     * Uses the format  +/- and the number of days. ie +1, 0 or -7
     *
     * @return string
     */
    public function getBackItTimeLeft()
    {
        $product = $this->getProduct();
        $datetime1 = new DateTime($product->getData(self::END));
        $datetime2 = new DateTime();

        if ($datetime1 > $datetime2) {
            return $datetime2->diff($datetime1)->format('%a');
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getShipDateText()
    {
        $product = $this->getProduct();

        return $product->getData(self::SHIP_DATE);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Exception
     */
    protected function getProduct()
    {
        if (!$this->product || !($this->product instanceof Mage_Catalog_Model_Product)) {
            $this->product = Mage::getModel('catalog/product');
        }

        return $this->product;
    }
}
