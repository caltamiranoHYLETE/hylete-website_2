<?php
class Icommerce_Attributebinder_Model_Attributebinder extends Mage_Core_Model_Abstract
{
    private $looks;

    public function _construct()
    {
        parent::_construct();
        $this->_init('attributebinder/attributebinder');
    }

    /*public function getValueCollection($attributeId){

        $collection=new Varien_Data_Collection();
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeId);
        foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
            $collection->addItem(array(
                "value" => $option['value'],
                "label" => $option['label'],
            ));

        }
        return $collection;
    }*/

    public function getBinderCollection($binderId){

        $collection=new Varien_Data_Collection();

        $read = Icommerce_Db::getDbRead();
   		$select = $read->select()
            ->from('icommerce_attributebinder_bindings')
            ->where('attributebinder_id=?', $binderId);

    	$rows = $read->fetchAll($select);
        $rowid = 1;
        foreach($rows as $row){
            $item = new Varien_Object();
            $item->setId($rowid);
            $item->setData("main_attribute_value",$row["main_attribute_value"]);
            $item->setData("bind_attribute_value",$row["bind_attribute_value"]);
            $collection->addItem($item);
            $rowid++;
        }

        return $collection;

    }

    //attributes for dropdowns
    public function getAttributes(){

        $attributeArray = array();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
             ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId());

        foreach ($attributes as $attribute) {

            $attributeArray[] = array(
                'value' => $attribute->getAttributeId(),
                'label' => $attribute->getFrontendLabel(),
            );
        }

        return $attributeArray;
        
    }

    public function getAttributeValues($attribute_id)
    {
        //'catalog_product'
        $attributeArray = array();
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_id);
        foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
            // prefix numeric strings with a space to prevent PHP to convert those array keys to integer, needed for correct comparison in select renderer and will be removed again before saving the values
            $optionValuePrefix = is_numeric($option['label']) ? ' ' : '';
            $attributeArray[$optionValuePrefix . $option['label']] = $option['label'];
        }
        return $attributeArray;
    }

    public function getAttributeValuesId($attribute_id)
    {
        //'catalog_product'
        $attributeArray = array();
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_id);
        foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
            $attributeArray[$option['value']] = $option['label'];
        }
        return $attributeArray;
    }

    /*
    public function getMainValueByBindValue($bind_attriubute_id, $bind_attribute_value){

        return Icommerce_Db::getValue("Select main_attribute_value from icommerce_attributebinder_bindings a INNER JOIN icommerce_attributebinder b ON a.attributebinder_id = b.id where bind_attribute_id = ".$bind_attriubute_id." and bind_attribute_value like '".$bind_attribute_value."'");

    }

    public function getMainValueIdByValue($main_attribute_id, $main_attribute_value){

        return Icommerce_Db::getValue("SELECT a.option_id FROM eav_attribute_option_value a INNER JOIN eav_attribute_option b ON a.option_id = b.option_id WHERE value = '".$main_attribute_value."' AND b.attribute_id = ".$main_attribute_id);
        
    }

    public function getMainValuesByBindValue( $bind_attribute_id, $bind_attribute_value ) { 
        return Icommerce_Db::getAssociative( "SELECT ea.attribute_code, binds.main_attribute_value, ab.default_main_attribute FROM icommerce_attributebinder_bindings as binds
                                              INNER JOIN icommerce_attributebinder as ab ON binds.attributebinder_id = ab.id
                                              INNER JOIN eav_attribute as ea ON ea.attribute_id=ab.main_attribute_id
                                              WHERE ab.bind_attribute_id=?
                                              AND binds.bind_attribute_value LIKE ?", array($bind_attribute_id, $bind_attribute_value) );
    }
    */

    public function getMainValueByBindAndValue( $bind_id, $bind_value ){
        return Icommerce_Db::getValue( "SELECT main_attribute_value FROM icommerce_attributebinder_bindings
                                        WHERE attributebinder_id=? AND bind_attribute_value=?", array($bind_id, $bind_value) );
    }

    
    public function saveBindingValues($main_attribute_values,$bind_attribute_values){

        Icommerce_Db::write("delete from icommerce_attributebinder_bindings where attributebinder_id = ".$this->getId());

        $sql = '';

        for($i = 0;$i<sizeof($bind_attribute_values);$i++){
            $sql .= 'insert into icommerce_attributebinder_bindings (attributebinder_id, main_attribute_value, bind_attribute_value) values ('.$this->getId().',"'.trim($main_attribute_values[$i]).'","'.$bind_attribute_values[$i].'");';
        }

        if($sql != ''){
            Icommerce_Db::write($sql);
        }
    }

    public function saveDefaultBindingValues($bind_attribute_values){

        Icommerce_Db::write("delete from icommerce_attributebinder_bindings where attributebinder_id = ".$this->getId());

        $sql = '';
        foreach($bind_attribute_values as $key=>$value){
            if($value != ''){
                $sql .= 'insert into icommerce_attributebinder_bindings (attributebinder_id, main_attribute_value, bind_attribute_value) values ('.$this->getId().',"","'.$value.'");';
            }
        }

        if($sql != ''){
            Icommerce_Db::write($sql);
        }
    }
}