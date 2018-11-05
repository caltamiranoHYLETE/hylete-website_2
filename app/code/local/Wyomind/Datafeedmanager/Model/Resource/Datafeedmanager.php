<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Datafeedmanager_Model_Resource_Datafeedmanager extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        // Note that the datafeedmanager_id refers to the key field in your database table.
        $this->_init('datafeedmanager/datafeedmanager', 'feed_id');
    }
    
    /**
     * Get entity_attribute_collection
     * 
     * @return array
     */
    public function getEntityAttributeCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $tableEet = $resource->getTableName('eav_entity_type');
        $select = $read->select()->from($tableEet)->where('entity_type_code=\'catalog_product\'');
        $data = $read->fetchAll($select);
        
        $typeId = $data[0]['entity_type_id'];
        
        /*  Liste des attributs disponible dans la bdd */
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($typeId)
                        ->addSetInfo()
                        ->getData();

        return $attributes;
    }
    
    public function importConfiguration($template)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $dfmc = $resource->getTableName('datafeedmanager_configurations');
        $sql = str_replace('{{datafeedmanager_configurations}}', $dfmc . ' ', $template);

        try {
            $writeConnection->query($sql);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('datafeedmanager')->__('The template has been imported.')
            );
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('datafeedmanager')->__("The template can't be imported.<br/>" . $e->getMessage())
            );
        }
    }
}
