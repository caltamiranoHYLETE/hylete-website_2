<?php

class TM_CheckoutSuccess_Block_Html_Bottom
    extends TM_CheckoutSuccess_Block_Template
{

    public function getOrder()
    {
        if (!$this->hasData('order')) {
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->getSuccessBlock()->getOrderId());
            $this->setData('order', $order);
        }
        return $this->getData('order');
    }

    public function getRecurringProfile()
    {
        if (!$this->hasData('recurring_profile')) {
            $recurringProfile = current(
                $this->getSuccessBlock()->getRecurringProfiles()
            );
            $this->setData('recurring_profile', $recurringProfile);
        }
        return $this->getData('recurring_profile');
    }

    public function getMapping()
    {
        if (!$this->hasData('mapping')) {

            $mapping = array();


            if ($this->getOrder()->getId()) {

                $order = $this->getOrder();
                $mapping['{{orderId}}'] = $order->getIncrementId();
                $mapping['{{orderAmount}}'] =
                    number_format($order->getGrandTotal(),2);
                $mapping['{{currency}}'] = $order->getOrderCurrencyCode();
                $mapping['{{customerId}}'] = $order->getCustomerId();
                if (!$mapping['{{customerId}}']) {
                    $mapping['{{customerEmail}}'] = $order->getCustomerEmail();
                }
                $mapping['{{paymentCode}}'] = $order->getPayment()
                    ->getMethodInstance()->getCode();
                $mapping['{{paymentTitle}}'] = $order->getPayment()
                    ->getMethodInstance()->getTitle();
                $mapping['{{shippingCode}}'] = $order->getShippingMethod();
                $mapping['{{shippingTitle}}'] = $order
                    ->getShippingDescription();

            } else {

                $recurringProfile = $this->getRecurringProfile();
                $mapping['{{orderId}}'] = $recurringProfile->getReferenceId();
                $mapping['{{orderAmount}}'] =
                    number_format($recurringProfile->getInitAmount(),2);
                $mapping['{{currency}}'] = $recurringProfile->getCurrencyCode();
                $mapping['{{customerId}}'] = $recurringProfile->getCustomerId();
                $mapping['{{paymentCode}}'] = $recurringProfile
                    ->getMethodCode();
                $mapping['{{paymentTitle}}'] = Mage::helper('payment')
                    ->getMethodInstance($mapping['{{paymentCode}}'])
                    ->getTitle();
                $mapping['{{shippingCode}}'] = NULL;
                $mapping['{{shippingTitle}}'] = NULL;

            }

            // get currency symbol from currency code
            $mapping['{{currencySymbol}}'] = Mage::app()->getLocale()
                ->currency($mapping['{{currency}}'])->getSymbol();

            // get cutomer Email if it is registered customer
            if (!isset($mapping['{{customerEmail}}'])) {
                $mapping['{{customerEmail}}'] =
                    Mage::getModel('customer/customer')
                        ->load($mapping['{{customerId}}'])->getEmail();
            }

            $this->setData('mapping', $mapping);
        }

        return $this->getData('mapping');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        // bottom HTML from config
        $html = Mage::getStoreConfig('checkoutsuccess/general/bottom_html');
        if (!$html) {
            return '';
        }
        foreach ($this->getMapping() as $key => $value) {
            $html = str_replace($key, $value, $html);
        }
        return $html;
    }

}
