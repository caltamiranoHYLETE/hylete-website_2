<?php

class TBT_Rewards_Block_Manage_Dashboard_Widget_Usage extends TBT_Rewards_Block_Manage_Dashboard_Widget_Template
{
    const CONFIG_DEV_MODE = 'rewards/platform/dev_mode';
    const FORMAT_DATE_DISPLAY = 'M j, Y';

    protected $_account = null;


    public function getAjaxUrl()
    {
        return $this->getUrl(
            'adminhtml/manage_dashboardWidget/checkUsage',
            array(
                '_forced_secure' => $this->getRequest()->isSecure()
            )
        );
    }

    public function getDomClassName()
    {
        return "st-usage";
    }

    public function getAjaxTemplate()
    {
        return "rewards/dashboard/widget/usage.phtml";
    }


    public function getAccountData()
    {
        if ($this->_account !== null) {
            return $this->_account;
        }

        try {
            $platform = Mage::getSingleton('rewards/platform_instance');
            $this->_account = $platform->account()->get();
        } catch (Exception $ex) {
            $this->_account = false;
        }

        return $this->_account;
    }

    /**
     * Retrieve percent of transactions used for current billing period
     *
     * @return int Percent of transactions made in current billing period
     */
    public function getPercentComplete()
    {
        $account = $this->getAccountData();
        if (isset($account['billing']['percent'])) {
            $percent = $account['billing']['percent'];
        }

        return is_numeric($percent) ? $percent : 0;
    }

    /**
     * Retrieve # of transactions used for current billing period
     *
     * @return int Number of transactions made in current billing period
     */
    public function getTransactionsUsed()
    {
        $account = $this->getAccountData();
        if (isset($account['billing']['transfers_used'])) {
            return $account['billing']['transfers_used'];
        }

        return -1;
    }

    /**
     * Retrieve current account billing period start date
     *
     * @return text Date on which current billing period starts
     */
    public function getBillingPeriodStart()
    {
        $account = $this->getAccountData();

        if (isset($account['billing']['start_date'])) {
            $timestamp = strtotime($account['billing']['start_date']);
            return date(self::FORMAT_DATE_DISPLAY, $timestamp);
        }

        return date(self::FORMAT_DATE_DISPLAY);
    }

    /**
     * Retrieve current account billing period end date
     *
     * @return text Date on which current billing period ends
     */
    public function getBillingPeriodEnd()
    {
        $account = $this->getAccountData();

        if (isset($account['billing']['end_date'])) {
            $timestamp = strtotime($account['billing']['end_date']);
            return date(self::FORMAT_DATE_DISPLAY, $timestamp);
        }

        return date(self::FORMAT_DATE_DISPLAY);
    }

    public function displayBillingPeriod()
    {
        $account = $this->getAccountData();

        // billing period will soon be returned by Platform, so we make sure it's currently returned
        if ($account === false || !isset($account['billing']['start_date']) || !isset($account['billing']['end_date'])) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the usage bar, transactions and billing period should be displayed
     *
     * @return boolean Returns true if account is connected and not in Developer Mode
     */
    public function displayUsage()
    {
        $account = $this->getAccountData();

        if ($account === false || $this->isDevMode()) {
            return false;
        }

        // just to be safe we check for billing data
        if (!isset($account['billing'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if account is connected and is in developer mode
     * @return boolean return true if account connected in Developer Mode
     */
    public function isDevMode()
    {
        $account = $this->getAccountData();
        return ($account !== false && Mage::getStoreConfig(self::CONFIG_DEV_MODE));
    }
}
