<?php
/**
 * Copyright (c) 2009-2012 Vaimo AB
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
 * @package     Icommerce_ImageBinder
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 */

class Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode
{
    const MATCHMODE_EXACT = 0;
    const MATCHMODE_CONTAINS = 1;
    const MATCHMODE_STARTSWITH = 2;
    const MATCHMODE_ENDSWITH = 3;

    public function toOptionArray()
    {
        return array(
            array('value' => self::MATCHMODE_EXACT, 'label' => Mage::helper('imagebinder')->__('Exact')),
            array('value' => self::MATCHMODE_CONTAINS, 'label' => Mage::helper('imagebinder')->__('Contains')),
            array('value' => self::MATCHMODE_STARTSWITH, 'label' => Mage::helper('imagebinder')->__('Starts with')),
            array('value' => self::MATCHMODE_ENDSWITH, 'label' => Mage::helper('imagebinder')->__('Ends with')),
        );
    }
}
