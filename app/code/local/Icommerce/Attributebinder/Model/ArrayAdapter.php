<?php
class Icommerce_Attributebinder_Model_ArrayAdapter extends AvS_FastSimpleImport_Model_ArrayAdapter
{
    public function __construct($data)
    {
        $this->_array = $data;
        $this->_applyAttributeBindingWhileImporting();
        $this->_position = 0;
    }

    private $_bindings = null;

    private function _applyAttributeBindingWhileImporting()
    {
        $model = Mage::getModel("attributebinder/attributebinder");
        if($this->_bindings == null){
            $this->_bindings = $model->getCollection();
        }

        foreach($this->_array as &$dataRow){

            if(isset($dataRow["sku"]) && $this->_bindings) {

                foreach ($this->_bindings as $item) {
                    if (isset($dataRow[$item->getBindAttributeCode()])) {
                        $bind_value = $dataRow[$item->getBindAttributeCode()];

                        if ($bind_value != "") {
                            $main_value = $model->getMainValueByBindAndValue($item->getData("id"), $bind_value);
                            $main_acode = $item->getData("main_attribute_code");

                            // only change if value is found...
                            if ($main_value) {
                                #if( $opt_id = Icommerce_Default::getOptionValueId($main_acode,$main_value) ){
                                #    Icommerce_Default::setOptionValue($dataRow["sku"],$main_acode, $opt_id);
                                #}
                                $dataRow[$main_acode] = $main_value;
                            } else {
                                if ($item->getDefaultMainAttribute() > 0) {
                                    #Icommerce_Default::setOptionValue($dataRow["sku"],$main_acode, $item->getDefaultMainAttribute());
                                    $dataRow[$main_acode] = $item->getDefaultMainAttribute();
                                }
                            }
                        }

                    }
                }
            }
        }
    }
}