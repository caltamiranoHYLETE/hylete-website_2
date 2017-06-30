<?PHP

class Icommerce_PageManager_Helper_Data extends Mage_Core_Helper_Abstract {

    const MAX_ALLOWED_FILE_SIZE = 512000; // Bytes
    const TARGET_PATH = 'upload/page/items/';

    private $allowedFileTypes = array('image/jpeg','image/pjpeg' ,'image/png', 'image/x-png', 'image/gif');
    private $allowedFileExtensions = array('jpg', 'jpeg', 'png', 'gif');

    private $statuses = array('0' => 'Not Active', '1' => 'Active');

    public function getStatuses(){
        return $this->statuses;
    }

    public function getRowTypeClass($rowType)
    {
        $configOptions = Mage::helper("pagemanager")->getConfigOptions("pagemanager/rowtypes");
        foreach($configOptions as $key=>$value){
            if($value["id"] == $rowType) return $key;
        }
        return "";
    }

    public function getRowTypeName($rowType)
    {
        $configOptions = Mage::helper("pagemanager")->getConfigOptions("pagemanager/rowtypes");
        foreach($configOptions as $key=>$value){
            if($value["id"] == $rowType) return Mage::helper('pagemanager')->__($value["label"]);
        }
        return "";
    }

    public function getRowImage($rowType){
        $configOptions = Mage::helper("pagemanager")->getConfigOptions("pagemanager/rowtypes");
        foreach($configOptions as $key=>$value){
            if($value["id"] == $rowType) return $value["image"];
        }
        return "";
    }


    public function getDefaultH1()
    {
        return Mage::helper('pagemanager')->__('The page H1, top heading (Click change to edit the text)');
    }

    /** Extendable headers
     * @return array
     */
    public function getHeadings()
    {
        $options = array();
        $configOptions = Mage::helper("pagemanager")->getConfigOptions("pagemanager/headings");
        foreach($configOptions as $key=>$value){
            $options[] = array('value'=>$key, 'label'=>Mage::helper('pagemanager')->__($value["label"]));
        }
        return $options;
    }

    public function getSortOptions()
    {
        $options = array();
        $configOptions = Mage::helper("pagemanager")->getConfigOptions("pagemanager/sort_options");
        foreach($configOptions as $key=>$value){
            $options[] = array('value'=>$key, 'label'=>Mage::helper('pagemanager')->__($value["label"]));
        }
        return $options;
    }

    public function getToplistOptions()
    {
        $options = array();
        $configOptions = Mage::helper("pagemanager")->getConfigOptions("pagemanager/toplist_options");
        foreach($configOptions as $key=>$value){
            $options[] = array('value'=>$key, 'label'=>Mage::helper('pagemanager')->__($value["label"]));
        }
        return $options;
    }

    public function getToplistTemplate($toplistTemplate){
        $configOptions = Mage::helper("pagemanager")->getConfigOptions("pagemanager/toplist_options");
        foreach($configOptions as $key=>$value){
            if($key == $toplistTemplate) return $toplistTemplate.".phtml";
        }
        return "";
    }

    public function getMaxAllowedFileSize(){
        return self::MAX_ALLOWED_FILE_SIZE;
    }

    public function getAbsoluteTargetPath(){
        return Mage::getBaseDir() .'/media/'. self::TARGET_PATH;
    }

    public function getTargetPath(){
        return self::TARGET_PATH;
    }

    public function getImageUrl($item){
        return str_replace('/index.php', '',Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$this->getTargetPath().$item['filename']);
    }

    public function getBigImageUrl($item){
        return str_replace('/index.php', '',Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$this->getTargetPath().$item['filename_big']);
    }

    public function isFileTypeAllowed($fileType){
        if( in_array($fileType, $this->allowedFileTypes) ){
            return true;
        }
        else {
            return false;
        }
    }

    public function isFileExtensionAllowed($fileExtension){
        if( in_array($fileExtension, $this->allowedFileExtensions) ){
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Activet HTML blocks or not
     *
     * @param   none
     * @return  bool
     */
    public function isHtmlActive()
    {
        return (bool)Mage::getStoreConfig('pagemanager/settings/html_active');
    }

    /**
     * Use extra classnames?
     * @return bool
     */
    public function useRowClassnames()
    {
        return (bool)Mage::getStoreConfig('pagemanager/settings/use_row_classnames');
    }

    /**
     * Get the predefined row classes from config xml
     *
     * @return array
     */
    public function getPredefRowClasses()
    {
        return explode(',', Mage::getStoreConfig('pagemanager/settings/predefined_row_classes'));
    }

    /**
     * Takes two arrays and unsets duplicates in $return
     *
     * @param $search array
     * @param $return array
     * @return array
     */
    public function unsetArrayDuplicates($search, $return)
    {
        foreach ($search as $s) {
            foreach ($return as $key => $r) {
                if ($s == $r) {
                    unset($return[$key]);
                }
            }
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function hasPredefClasses()
    {
        return count($this->getPredefRowClasses() > 0) ? true : false;
    }

    /** Get config options
     * @param $xmlPath
     * @return array|string
     */
    public function getConfigOptions($xmlPath){
        $configOptions = Mage::getConfig()->getNode($xmlPath)->asArray();
        $deletes = array();
        if(isset($configOptions)){
            foreach($configOptions as $key=>$value){
                if(isset($value["remove"])) $deletes[] = $key;
            }
            foreach($deletes as $delete){
                unset($configOptions[$delete]);
            }
            return $configOptions;
        }
    }

    public function getPageIdFromName($name){
        $id = Mage::getModel("pagemanager/page")->getPageIdFromName($name);
        if(!isset($id)) $id = 0;
        return $id;
    }

}
