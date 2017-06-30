<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Carbon
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @comment     Complementary module to theme_carbon
 */

class Vaimo_Carbon_Model_System_Config_Source_Icon_Font
{
    public function toOptionArray()
    {
        $supportedFonts = Mage::helper('carbon')->getIconFonts();

        $options = array();
        foreach ($supportedFonts as $code => $data) {
            $options[] = array('value' => $code, 'label' => Mage::helper('carbon')->__($data['label']));
        }

        $options[] = array('value' => 'disable', 'label' => 'Disable Font Icons');

        return $options;
    }
}