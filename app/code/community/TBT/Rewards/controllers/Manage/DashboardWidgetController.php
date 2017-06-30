<?php

class TBT_Rewards_Manage_DashboardWidgetController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }
    
    public function checkUsageAction()
    {
        $result = $this->getLayout()->createBlock('rewards/manage_dashboard_widget_usage')->getAjaxHtml();

        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        $this->getResponse()->setBody(Zend_Json::encode($result));

        return $this;
    }
}