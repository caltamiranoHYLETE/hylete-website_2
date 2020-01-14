<?php
class Hylete_ServiceLeague_Block_Index extends Vaimo_Hylete_Block_Widgets_Signup_Extended{

    public function getResponse(){

        Mage::log("in block get response", null, 'govx-auth.log');
        $response = Mage::getSingleton('serviceleague/verifier')->getResponse();
        Mage::log($response, null, 'govx-auth.log');
        $response = json_decode($response,true);
        if($response['verification']['status']== "Approved"){
            if(isset($response['userProfile'])){
                return $response['userProfile'];
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

}