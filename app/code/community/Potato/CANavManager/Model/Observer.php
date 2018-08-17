<?php
class Potato_CANavManager_Model_Observer
{
    public function adminSystemConfigSaveSettings()
    {
        $params = Mage::app()->getRequest()->getParams();
        if (!isset($params['section']) || $params['section'] != 'po_canm' || !isset($params['type'])) {
            return $this;
        }

        $groupsPost = Mage::app()->getRequest()->getPost('groups');
        if (!isset($groupsPost['account'])) {
            $field = array('links' => array('value' => serialize($params['type'])));
            $fields = array('fields' => $field);
            $groupsPost['account'] = $fields;
        }
        Mage::app()->getRequest()->setPost('groups', $groupsPost);

        return $this;
    }

    public function prepareLinksForFrontend()
    {
        if (!Mage::helper('po_canm')->isEnabled()) {
            return $this;
        }
        $navBlock = Mage::app()->getLayout()->getBlock('customer_account_navigation');
        if (!$navBlock) {
            return $this;
        }
        $links = $navBlock->getLinks();
        $customLinks = Mage::helper('po_canm')->getLinksSetting();

        //add custom links to array
        foreach ($links as $name => $link) {
            if (array_key_exists($name, $customLinks)) {
                continue;
            }
            $customLinks[$name] = $link;
        }

        $customNavBlock = Mage::app()->getLayout()->createBlock('customer/account_navigation');
        $customNavBlock->setTemplate($navBlock->getTemplate());
        foreach ($customLinks as $name => $customLink) {
            if (!array_key_exists($name, $links)) {
                continue;
            }
            if (!isset($customLink['visible']) || !$customLink['visible']) {
                continue;
            }
            $link = $links[$name];
            if (!isset($customLink['path'])) {
                $customLink['path'] = $link->getPath();
            }
            if (!isset($customLink['url_params'])) {
                $customLink['url_params'] = array();
            }
            $customNavBlock->addLink($name, $customLink['path'], $customLink['label'], $customLink['url_params']);
        }

        //set url from default navigation block
        foreach ($customNavBlock->getLinks() as $name => $customLink) {
            if (!array_key_exists($name, $links)) {
                    continue;
            }
            $link = $links[$name];
            $customLink->setUrl($link->getUrl());
        }
        Mage::app()->getLayout()->setBlock('customer_account_navigation', $customNavBlock);
        return $this;
    }
}