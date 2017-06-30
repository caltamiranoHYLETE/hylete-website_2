<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_Blog
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */

class Vaimo_Blog_Block_Product_View_Categorylink extends Mage_Core_Block_Template
{

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function getCategory()
    {
        $currentCategory = Mage::registry('current_category');

        if (!isset($currentCategory)) {
            $currentProduct = Mage::registry('current_product');
            $currentCategory = $currentProduct
                    ->getCategoryCollection()
                    ->getFirstItem();
        }
        return $currentCategory;
    }

    public function getCategoryUrl()
    {
        return $this
                ->getCategory()
                ->getUrl();
    }

    public function getCategoryName()
    {
        return $this
                ->getCategory()
                ->getName();
    }
}
