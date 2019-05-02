<?php
class Livechat_Livechat_Adminhtml_LivechatController extends Mage_Adminhtml_Controller_Action
{
 
    public function indexAction() 
    {
            
        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'livechat_config',
            array('template' => 'livechat/livechat_config.phtml')
        );
        
        $this->loadLayout()
            ->_setActiveMenu('livechat')
            ->_addContent($block)
            ->renderLayout();
    }
    public function postAction() 
    {
        
        //avoid notices warnings
        $livechat_license_number = filter_input(INPUT_POST, 'livechat_license_number', FILTER_SANITIZE_SPECIAL_CHARS);
        $livechat_login = filter_input(INPUT_POST, 'livechat_login', FILTER_SANITIZE_SPECIAL_CHARS);
        $livechat_mobile = filter_input(INPUT_POST, 'livechat_mobile', FILTER_SANITIZE_SPECIAL_CHARS);
        $livechat_sounds = filter_input(INPUT_POST, 'livechat_sounds', FILTER_SANITIZE_SPECIAL_CHARS);
        $livechat_cart = filter_input(INPUT_POST, 'livechat_cart', FILTER_SANITIZE_SPECIAL_CHARS);

        !isset($livechat_license_number) ? $livechat_license_number = ' ' : $livechat_license_number;
        !isset($livechat_login) ? $livechat_login = ' ' : $livechat_login;
        !isset($livechat_mobile) ? $livechat_mobile = 'No' : $livechat_mobile;  
        !isset($livechat_sounds) ? $livechat_sounds = 'No' : $livechat_sounds;  
        !isset($livechat_cart) ? $livechat_cart = 'Yes' : $livechat_cart;  
        
        $config_table = Mage::getSingleton('core/resource')->getTableName('core_config_data');
        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = 'SELECT * FROM ' . $config_table;
        $query .= ' WHERE scope="default" AND scope_id=0 AND path="livechat/general/license"';
        $results = $read->fetchAll($query);

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        //check for existing configurations
        if ($row = array_pop($results)) {
            $license_id = $row['config_id'];
            
            $query = 'UPDATE ' . $config_table;
            $query .= ' SET value="' . $livechat_license_number . '"';
            $query .= ' WHERE config_id=' . $license_id;
            $write->query($query);
            
            $query = 'UPDATE ' . $config_table;
            $query .= ' SET value="' . $livechat_login . '"';
            $query .= ' WHERE config_id=' . ++$license_id;
            $write->query($query);
            
            $query = 'UPDATE ' . $config_table;
            $query .= ' SET value="' . $livechat_mobile . '"';
            $query .= ' WHERE config_id=' . ++$license_id;
            $write->query($query);
            
            $query = 'UPDATE ' . $config_table;
            $query .= ' SET value="' . $livechat_sounds . '"';
            $query .= ' WHERE config_id=' . ++$license_id;
            $write->query($query);
            
            $query = 'UPDATE ' . $config_table;
            $query .= ' SET value="' . $livechat_cart . '"';
            $query .= ' WHERE config_id=' . ++$license_id;
            $write->query($query);
        } else {
            $query = 'INSERT INTO ' . $config_table;
            $query .= ' (scope, scope_id, path, value)';
            $query .= ' VALUES ("default", 0, "livechat/general/license", "' . $livechat_license_number . '"),';
            $query .= ' ("default", 0, "livechat/general/login", "' . $livechat_login . '"),';
            $query .= ' ("default", 0, "livechat/general/mobile", "' . $livechat_mobile . '"),';
            $query .= ' ("default", 0, "livechat/general/sounds", "' . $livechat_sounds . '"),';
            $query .= ' ("default", 0, "livechat/general/cart", "' . $livechat_cart . '")';
            
            
            $write->query($query);
        }
    
        //bad practice!
        Mage::app()->getCacheInstance()->flush();
        
        $this->_redirect('*/*/index');
    }
    
    public function resetAction() 
    {
        
        $config_table = Mage::getSingleton('core/resource')->getTableName('core_config_data');
        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = 'SELECT * FROM ' . $config_table;
        $query .= ' WHERE scope="default" AND scope_id=0 AND path="livechat/general/license"';
        $results = $read->fetchAll($query);

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $row = array_pop($results);
        
        $license_id = $row['config_id'];
            
        $query = 'DELETE FROM core_config_data WHERE path = '.'"livechat/general/license"';
        $write->query($query);

        $query = 'DELETE FROM core_config_data WHERE path = '.'"livechat/general/login"';
        $write->query($query);
        
        $query = 'DELETE FROM core_config_data WHERE path = '.'"livechat/general/mobile"';
        $write->query($query);

        $query = 'DELETE FROM core_config_data WHERE path = '.'"livechat/general/sounds"';
        $write->query($query);
        
        $query = 'DELETE FROM core_config_data WHERE path = '.'"livechat/general/cart"';
        $write->query($query);
    
        //bad practice!
        Mage::app()->getCacheInstance()->flush();
        
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return true;
    }
}