<?php

class Icommerce_Attributebinder_Model_Observer {

    public function __construct()
    {
    }

    public function applyAttributeBinderBindings($observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();

        // fetch all bindings
        $model = Mage::getModel("attributebinder/attributebinder");
        $collection = $model->getCollection();

        // fetch all bindings
        $model = Mage::getModel("attributebinder/attributebinder");
        $collection = $model->getCollection();

        // loop though all attributes
        foreach($collection as $item){
            $bind_value = $product->getAttributeText($item->getBindAttributeCode());
            if($bind_value != ""){
                $main_value = $model->getMainValueByBindAndValue( $item->getData("id"),$bind_value );
                $main_acode = $item->getData("main_attribute_code");
                // only change if value is found...
                if( $main_value ){
                    if ($item->getData("suppress_man_main_attr") == 1 && $product->getData($item->getBindAttributeCode()) == $product->getOrigData($item->getBindAttributeCode())){
                        continue;
                    }
                    if( $opt_id = Icommerce_Default::getOptionValueId($main_acode,$main_value) ){
                        $product->setData( $main_acode, $opt_id);
                    }
                } else {
                    if ($item->getDefaultMainAttribute()>0) {
                        $product->setData($main_acode, $item->getDefaultMainAttribute());
                    }
                }
            }
        }

    }

    public function rescanAttributeBinders(Mage_Cron_Model_Schedule $schedule, $forceRun = false)
    {
        try {
            //fetch all bindings
            $model = Mage::getModel("attributebinder/attributebinder");
            $collection = $model->getCollection();

            $sql = '';
            //loop though all attributes
            foreach($collection as $item){
                $attrarr = Mage::getModel('attributebinder/attributebinder')->getAttributeValuesId($item->getBindAttributeId(), true);
                foreach ($attrarr as $id => $a) {
                    if ($a != '') {
                        $escapedSqlValue = Icommerce_Db::wrapQueryValues($a);
                        if (!Icommerce_Db::getValue( "SELECT attributebinder_id FROM icommerce_attributebinder_bindings WHERE attributebinder_id='" . $item->getId() . "' AND bind_attribute_value=" . $escapedSqlValue . "" )) {
                            $sql .= 'insert into icommerce_attributebinder_bindings (attributebinder_id, main_attribute_value, bind_attribute_value) values (' . $item->getId() . ', "",' . $escapedSqlValue . ');';
                        }
                    }
                }
            }
            if($sql != ''){
                Icommerce_Db::write($sql);
            }
        } catch (Exception $e) {
            Mage::printException($e);
        }
    }

}