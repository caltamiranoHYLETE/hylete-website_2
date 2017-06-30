<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
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
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 * @author      Wilko Nienhaus
 */

class Icommerce_QuickCheckout_AgreementsController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get agreements output for lyteframe
     *
     * @return Icommerce_QuickCheckout_AgreementsController
     */
    public function showAgreementsAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock(
            'Mage_Checkout_Block_Agreements',
            'checkout.onepage.agreements',
            array('template' => 'checkout/onepage/agreements-quickcheckout.phtml')
        );


        $_agreements = $block->getAgreements();

        $_output = "";

        foreach($_agreements as $_a){

            if ($_a->getIsHtml()){
                $_output = $_a->getContent();
            }
            else {
                $_output = nl2br($block->htmlEscape($_a->getContent()));
            }
        }
        
        $this->getResponse()->setBody($_output);

        return $this;
    }
}