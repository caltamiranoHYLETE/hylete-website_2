<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Icommerce_QuickCheckout
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Icommerce_QuickCheckout_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage
{
    protected $_enumeratedSteps = array('payment', 'shipping_method', 'billing');

    public function getEnumeratedSteps()
    {
        return $this->getDataSetDefault('enumerated_steps', $this->_enumeratedSteps);
    }

    protected function _getStepCodes()
    {
        $stepCodes = parent::_getStepCodes();

        if (Mage::helper('quickcheckout')->paymentAsFirstStep()) {
            $firstSteps = array('login', 'payment');

            $_stepsToBringToFront = array_reverse($firstSteps);
            foreach ($_stepsToBringToFront as $stepCode) {
                $index = array_search($stepCode, $stepCodes);
                unset($stepCodes[$index]);
                array_unshift($stepCodes, $stepCode);
            }
        }

        return $stepCodes;
    }

    public function getSteps()
    {
        $steps = parent::getSteps();

        $index = 1;
        $enumerateSteps = array_flip($this->getEnumeratedSteps());
        foreach ($steps as $stepId => $step) {
            if (isset($enumerateSteps[$stepId])) {
                $stepBlock = $this->getChild($stepId);
                if ($stepBlock && $stepBlock->isShow()) {
                    $stepBlock->setStepIndex($index++);
                }
            }
        }

        return $steps;
    }
}