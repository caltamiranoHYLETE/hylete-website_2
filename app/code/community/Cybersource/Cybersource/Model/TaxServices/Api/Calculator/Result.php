<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Result
{
    const RESULT_SUCCESSFUL_DECISION = 'ACCEPT';

    const RESULT_RATE = 'rate';
    const RESULT_TAXABLE = 'taxable';
    const RESULT_TAXRESULT = 'taxAmount';

    const JURISDICTION_CODE = 'code';
    const JURISDICTION_NAME = 'name';
    const JURISDICTION_PERCENT = 'percent';
    const JURISDICTION_RATE = 'rate';
    const JURISDICTION_POSITION = 'position';
    const JURISDICTION_PRIORITY = 'priority';

    private $result;

    private $jurisdictions = array();

    /**
     * Sets the instance of the result type.  This method should not need to be called unless there is a significant
     * customization to how tax handling is managed
     *
     * @param $result
     */
    public function setApiResult($result)
    {
        $this->result = $result;
    }

    /**
     * Provides the raw, unprocessed, data from the result call.
     *
     * @return mixed
     */
    public function getRawResult()
    {
        return $this->result;
    }

    /**
     * Does the result object indicate a successful API call?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return
            isset($this->result->decision)
            && $this->result->decision == self::RESULT_SUCCESSFUL_DECISION
            && isset($this->result->taxReply->totalTaxAmount);
    }

    /**
     * Retrieves the total tax amount for the entire API call or zero if the API call is not a success
     *
     * @return float
     */
    public function getTotalTaxAmount()
    {
        if ($this->isSuccess()) {
            return $this->result->taxReply->totalTaxAmount;
        }
        return 0;
    }

    /**
     * Retrieves the total amount for the entire API call  or zero if the API call is not a success
     *
     * @return float
     */
    public function getTotalAmount()
    {
        if ($this->isSuccess()) {
            return $this->result->taxReply->grandTotalAmount;
        }
        return 0;
    }

    /**
     * Retrieves the sum of the total amount and the total tax amount or zero if the API call is not a success
     *
     * @return float
     */
    public function getSubtotal()
    {
        if ($this->isSuccess()) {
            return $this->getTotalAmount() - $this->getTotalTaxAmount();
        }
        return 0;
    }

    /**
     * Iterates over all of the jurisdictions returned in the API call and calculates the total percentage that Magento
     * needs to calculate for tax.   Returns zero if the API call is not a success
     *
     * @return float
     */
    public function getTotalTaxPercentage()
    {
        if ($this->isSuccess()) {
            $jurisdictions = $this->getJurisdictions();
            if ($jurisdictions) {
                $taxPercentage = 0;
                foreach ($jurisdictions as $jurisdiction) {
                    $taxPercentage += $jurisdiction[self::RESULT_RATE];
                }
                return round($taxPercentage * 100);
            } else {
                $cartTotal = $this->getSubtotal();
                $totalTax = $this->getTotalTaxAmount();
                if ($cartTotal > 0 && $totalTax > 0) {
                    $taxPercentage = ($totalTax / $cartTotal) * 100;
                    return round($taxPercentage, 2);
                }
            }
        }
        return 0;
    }

    /**
     * Returns a list of jurisdictions and their *total* tax.  So if you have multiple line items this method will
     * sum the total tax for each jurisdiction by iterating over each line item.
     *
     * @return array
     */
    public function getJurisdictions()
    {
        if (!$this->jurisdictions) {
            if (isset($this->result->taxReply->item)) {
                $items = $this->result->taxReply->item;
                if (isset($items->jurisdiction)) {
                    $items = array($items);
                }
                foreach ($items as $taxItem) {
                    if (isset($taxItem->jurisdiction)) {
                        foreach ($taxItem->jurisdiction as $jurisdiction) {
                            $key = $jurisdiction->country
                                . '|' . $jurisdiction->region
                                . '|' . $jurisdiction->code;
                            if (!isset($this->jurisdictions[$key])) {
                                $this->jurisdictions[$key] = (array)$jurisdiction;
                            } else {
                                $this->jurisdictions[$key][self::RESULT_TAXABLE] += $jurisdiction->taxable;
                                $this->jurisdictions[$key][self::RESULT_TAXRESULT] += $jurisdiction->taxAmount;
                            }
                        }
                    }
                }
            }
        }
        return $this->jurisdictions;
    }

    /**
     * Retrieves the tax rates for each jurisdiction
     *
     * @return array
     */
    public function getJurisdictionTaxRates()
    {
        $jurisdictions = $this->getJurisdictions();
        $result = array();
        $item = 0;
        foreach ($jurisdictions as $jurisdiction) {
            $result[] = array(
                self::JURISDICTION_CODE => $jurisdiction[self::JURISDICTION_NAME],
                self::JURISDICTION_PERCENT => round($jurisdiction[self::JURISDICTION_RATE] * 100, 4),
                self::JURISDICTION_POSITION => $item,
                self::JURISDICTION_PRIORITY => $item,
            );
            $item++;
        }
        return $result;
    }
}
