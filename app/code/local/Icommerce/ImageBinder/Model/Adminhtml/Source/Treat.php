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

class Icommerce_ImageBinder_Model_Adminhtml_Source_Treat
{
    const TREAT_IMPORTED_NOTHING = 0;
    const TREAT_IMPORTED_MOVE = 1;
    const TREAT_IMPORTED_DELETE = 2;

    public function toOptionArray()
    {
        return array(
            array('value' => self::TREAT_IMPORTED_NOTHING, 'label' => Mage::helper('imagebinder')->__('Do nothing')),
            array('value' => self::TREAT_IMPORTED_MOVE, 'label' => Mage::helper('imagebinder')->__('Move to _imported folder')),
            array('value' => self::TREAT_IMPORTED_DELETE, 'label' => Mage::helper('imagebinder')->__('Delete')),
        );
    }
}
