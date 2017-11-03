<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group. All rights reserved.
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
 * @copyright   Copyright (c) 2009-2017 Vaimo AB
 */

class Vaimo_Hylete_Model_Amrules_Promotions extends Amasty_Rules_Model_Promotions {
    /**
     * Adds a detailed description of the discount
     *
     * @param $address
     * @param $rule
     * @param $item
     * @param $discount
     *
     * @return $this
     */
    protected function _addFullDescription($address, $rule, $item, $discount)
    {
        // we need this to fix double prices with one step checkouts
        $ind = $rule->getId() . '-' . $item->getId();
        if (isset($this->descrPerItem[$ind])) {
            return $this;
        }
        $this->descrPerItem[$ind] = true;

        $descr = $address->getFullDescr();
        if (!is_array($descr)) {
            $descr = array();
        }

        if (empty($descr[$rule->getId()])) {

            $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
            if (!$ruleLabel) {
                if (Mage::helper('ambase')->isModuleActive('Amasty_Coupon')) {
                    if (!$ruleLabel) {
                        $ruleLabel = $rule->getCouponCode(); // possible wrong code, known issue
                    }
                } else { // most frequent case
                    // take into account "generate and import amasty extension"
                    //	UseAutoGeneration
                    if ($rule->getUseAutoGeneration() || $rule->getCouponCode()) {
                        $ruleLabel = $rule->getCouponCode();
                    }
                }
            }

            if (!$ruleLabel) {
                $ruleLabel = $rule->getName();
            }

            $descr[$rule->getId()] =
                array('label' => '<div class="amrules-discount"><strong>' . htmlspecialchars($ruleLabel) . ':</strong></div>', 'amount' => 0);
        }
        // skip the rule as it adds discount to each item
        // version before 1.4.1 has no class constants for actions
        $skipTypes = array('cart_fixed', Amasty_Rules_Helper_Data::TYPE_AMOUNT);

        if (!in_array($rule->getSimpleAction(), $skipTypes)
            && Mage::getStoreConfig('amrules/breakdown_settings/breakdown_products')
        ) {
            $sep = ($descr[$rule->getId()]['amount'] > 0) ? ', <br/> ' : '';
            $descr[$rule->getId()]['label'] = $descr[$rule->getId()]['label']
                . $sep . htmlspecialchars($item->getName());
        }

        $descr[$rule->getId()]['amount'] += $discount;
        $address->setFullDescr($descr);

    }
}
