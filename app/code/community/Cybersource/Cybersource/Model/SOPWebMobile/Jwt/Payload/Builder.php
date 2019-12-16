<?php

class Cybersource_Cybersource_Model_SOPWebMobile_Jwt_Payload_Builder
{
    /**
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $order
     * @param null|string $cardBin
     * @return array
     */
    public function build($order, $cardBin = null)
    {
        $result = [
            'OrderDetails' => [
                'OrderNumber' => $order->getReservedOrderId() ? $order->getReservedOrderId() : $order->getIncrementId(),
                'CurrencyCode' => $order->getQuoteCurrencyCode() ? $order->getQuoteCurrencyCode() : $order->getOrderCurrencyCode(),
                'Amount' => round($order->getGrandTotal() * 100),
                'OrderChannel' => 'S'
            ]
        ];

        $billingAddress = $order->getBillingAddress();

        $result['Consumer']['Email1'] = $billingAddress->getEmail();
        $result['Consumer']['BillingAddress'] = $this->buildAddress($billingAddress);

        if ($cardBin) {
            $result['Consumer']['Account']['AccountNumber'] = $cardBin;
        }

        if ($shippingAddress = $order->getShippingAddress()) {
            $result['Consumer']['ShippingAddress'] = $this->buildAddress($shippingAddress);
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address|Mage_Sales_Model_Order_Address $address
     * @return array
     */
    private function buildAddress($address)
    {
        return [
            'FirstName' => $address->getFirstname(),
            'LastName' => $address->getLastname(),
            'Address1' => $address->getStreet(1),
            'Address2' => $address->getStreet(2),
            'City' => $address->getCity(),
            'State' => $address->getRegionCode(),
            'CountryCode' => $address->getCountryId(),
            'Phone1' => $address->getTelephone(),
            'PostalCode' => $address->getPostcode()
        ];
    }
}
