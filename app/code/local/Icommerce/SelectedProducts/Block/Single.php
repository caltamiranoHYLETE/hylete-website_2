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
 * @package     Vaimo_Module
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */
class Icommerce_SelectedProducts_Block_Single extends Icommerce_SelectedProducts_Block_Widget
    implements Mage_Widget_Block_Interface
{
    public function getCollection($attribute = "all", $num_get = 3, $desc = true, $attribs = array("entity_id", "sku", "image", "name"), $attributesToFilter = array(), $instock_only = 0, $xtra_options = array())
    {
        if ($idPath = $this->getIdPath()) {
            $productId = sscanf($idPath, 'product/%s');

            $this->setPageContent(array_pop($productId));
        }

        $attribute = 'manual_products';
        $attribs[] = 'small_image';
        $attribs[] = 'thumbnail';

        return parent::getCollection($attribute, $num_get = 1, $desc, $attribs, $attributesToFilter, $instock_only, $xtra_options);
    }
}