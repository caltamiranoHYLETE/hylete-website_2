<?php

/**
 * Class Globale_Order_Model_Addresses
 */
class Globale_FixedPrices_Model_Update extends Mage_Core_Model_Abstract
{

	/**
	 * save file to uploads folder
	 * @param boolean $Delete delete missing rows from DB
	 */
	public function saveFile($Delete)
    {
        if (!empty($_FILES['csv']['name'])) {
            try {
                $Uploader = new Mage_Core_Model_File_Uploader('csv');
                $Uploader->setAllowedExtensions(array('csv'));
                $Uploader->setAllowRenameFiles(true);
                $this->addValidators( $Uploader );
                $UploadDir = Mage::getBaseDir('var') . DS . 'uploads' . DS . 'globale' . DS;
                $Result = $Uploader->save($UploadDir);
                $File = $UploadDir . $Result['file'];

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return;
            }

            $Data = $this->csvToArray($File);
            if(!empty($Data)){
                $this->updateDB($Data, $Delete);
            }
            else{
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('File Was Empty! Please Check your file data and try again.'));
            }

        }
    }

	/**
	 * add validation to file size
	 * @param Mage_Core_Model_File_Uploader $Uploader
	 */
    protected function addValidators(Mage_Core_Model_File_Uploader $Uploader) {

        $Uploader->addValidateCallback('size', $this, 'validateMaxSize');
    }

	/**
	 * convert csv data to array
	 * @param string $Filename
	 * @param string $Delimiter
	 * @return array|bool
	 */
    protected function csvToArray($Filename = '', $Delimiter = ',') {

        //if not set in php.ini make php detect line endings.
        ini_set("auto_detect_line_endings", true);

        if (!file_exists($Filename) || !is_readable($Filename))
            return FALSE;

        $Header = NULL;
        $Data = array();
        if (($Handle = fopen($Filename, 'r')) !== FALSE) {
            while (($Row = fgetcsv($Handle, 1000, $Delimiter)) !== FALSE) {
                if (!$Header){
                    $Header = $Row;
                } else {
                    $Arr = array_combine($Header, $Row);
                    //fix for PHP-577 if special_price is left blank since its a Decimal 12,4 field it will not default to null but to 0.
                    if($Arr['special_price'] === ''){
                        $Arr['special_price'] = null;
                    }
                    $Key = $this->generateDataKey($Arr);
                    $Data[$Key] = $Arr;
                }
            }
            fclose($Handle);
        }
        return $Data;
    }

	/**
	 * update Magento DB with fixed prices data
	 * @param array $Data array of fixed prices data
	 * @param boolean $Delete delete missing rows from DB
	 */
    protected function updateDB($Data, $Delete){

        $RowsDelete = 0;
        $RowsInsert = 0;
        $RowsUpdate = 0;

        /** @var Globale_FixedPrices_Model_Fixedprices $Model */
        $Model = Mage::getModel('globale_fixedprices/fixedprices');
        try {
            $FixedPriceCollection = $Model->getCollection();


            if($FixedPriceCollection->getSize() && $Delete){
                foreach ($FixedPriceCollection as $Item) {
                	/**@var Globale_FixedPrices_Model_Fixedprices $Item */
                    $Key = $this->generateDataKey($Item->getData());
                    if (!array_key_exists($Key, $Data)) {
                        $Item->delete();
                        $RowsDelete++;
                    }
                }
                $FixedPriceCollection->save();
            }

            foreach ($Data as $Row){
                $Model->setData($Row);
				$Model->loadByFixedProduct($Row['product_code'], $Row['country_code'], $Row['currency_code']);

                if($Model->getId()){
                    $RowsUpdate++;
                }
                else{
                    $RowsInsert++;
                }

                $Model->save()->unsetData();
            }

            if($Delete){
                Mage::getSingleton('core/session')->addNotice(Mage::helper('core')->__('Rows Deleted : ') . $RowsDelete);
            }
            Mage::getSingleton('core/session')->addNotice(Mage::helper('core')->__('Rows Updated : ') . $RowsUpdate);
            Mage::getSingleton('core/session')->addNotice(Mage::helper('core')->__('Rows Inserted : ') . $RowsInsert);
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('core')->__('Fixed Prices Products Updated Succefully!'));

        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

    }

	/**
	 * create a filter key for fixed priced product based on Global-e CCC
	 * @param array $arr
	 * @return string
	 */
    protected function generateDataKey($arr){
        return "{$arr['product_code']}_{$arr['country_code']}_{$arr['currency_code']}";
    }

}