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
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist <kjell.holmqvist@vaimo.com>
 */

class Vaimo_AppApi_Model_Observer
{
    public function checkoutSuccess(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order && $order->getId()) {
                $payment = $order->getPayment();
                $url = $payment->getAdditionalInformation('redirect_to_app_url');
                if ($url && strlen($url)>2) { // strlen just to make sure it's not corrupt
                    $realUrl = Mage::getUrl($url,
                        array(
                            'order_id' => $order->getId(),
                            'increment_id' => $order->getIncrementId(),
                        )
                    );
                    if ($realUrl) {
                        Mage::app()->getResponse()->setRedirect($realUrl);
                    }
                }
            }
        }
    }
}
