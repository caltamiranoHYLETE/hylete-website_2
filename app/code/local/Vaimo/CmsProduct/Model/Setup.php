<?php
/**
 * Copyright (c) 2009-2016 Vaimo Group
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
 * @package     Vaimo_CmsProduct
 * @copyright   Copyright (c) 2009-2016 Vaimo Group
 * @author      Andreas Wickberg <andreas.wickberg@vaimo.com>
 */

class Vaimo_CmsProduct_Model_Setup extends Mage_Eav_Model_Entity_Setup
{
    const EAV_ATTR_SET = 'Grid Products';
    const CMS_BLOCK_CODE = 'cms_block';
    const CMS_BLOCK_ALIGN_CODE = 'cms_block_align';

    /**
     * Prepare attribute values to save
     * core has a fixed set of attributes that can be set programatically. We want to add
     * - used_in_product_listing to make the attribute available for FE
     * - apply_to to ensure that only the Grid Producs attribute set has these
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        if (isset($attr['used_in_product_listing']))
            $data['used_in_product_listing'] = $this->_getValue($attr, 'used_in_product_listing');
        if (isset($attr['apply_to']))
            $data['apply_to'] = $this->_getValue($attr, 'apply_to');

        return $data;
    }

}
