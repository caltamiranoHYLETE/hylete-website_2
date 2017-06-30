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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Helper_Type extends Mage_Core_Helper_Abstract
{
    const VERTICAL = 1;
    const HORIZONTAL = 2;

    const XPATH_HORIZONTAL_ENABLED = 'multioptionfilter/settings/horizontal_filters';

    public function change($layout, $type)
    {
        $update = $layout->getUpdate();

        if (count((array)$layout->getNode())) {
            throw Mage::exception('Vaimo_MultiOptionFilter', 'Layout type changed after layout blocks generated');
        }

        switch ($type) {
            case self::HORIZONTAL:
                $update->addHandle('mof_horizontal');

                break;
            case self::VERTICAL:
                $update->removeHandle('mof_horizontal');

                break;
            default:
                throw Mage::exception('Vaimo_MultiOptionFilter', 'Unknown layer layout type');

                break;
        }
    }
}