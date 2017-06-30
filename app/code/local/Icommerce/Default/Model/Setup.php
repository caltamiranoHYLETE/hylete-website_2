<?php
/**
 * Created by JetBrains PhpStorm.
 * User: arne
 * Date: 2011-11-04
 * Time: 05.47
 * To change this template use File | Settings | File Templates.
 */
 
class Icommerce_Default_Model_Setup extends Mage_Core_Model_Resource_Setup  {

    public function getPreviousVersion(){
        return $this->_getResource()->getDataVersion($this->_resourceName);
    }

    public function getNextVersion(){
        return (string)$this->_moduleConfig->version;
    }

}

