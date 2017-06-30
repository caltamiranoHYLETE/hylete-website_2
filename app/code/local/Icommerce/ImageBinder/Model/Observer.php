<?php

include_once 'lib/Icommerce/Utils/StrUtils.php';
include_once 'lib/Icommerce/Utils/ArrayUtils.php';
include_once 'lib/Icommerce/Utils/FileUtils.php';
/**
 * ImageBinder Observer
 *
 * @category   Icommerce
 * @package    Icommerce_ImageBinder
 * @author     Icommerce <arne@icommerce.se>
 */
class Icommerce_ImageBinder_Model_Observer
{
    static $SFTP_PATH = null;
    //static $SFTP_PATH = '/mnt/www/ha1/media/import/';
    static $st_did_import = FALSE;
    protected $_media_path;

    protected $_oldMagento = false;
    protected $_silent = false;
    protected $_delete_before_import = true;

    protected $_regex;
    protected $_attrField;
    protected $_sortField;
    protected $_typeField;
    protected $_sortDir;
    protected $_valueTable;
    protected $_whereTmpl;
    protected $_whereAttrFormat;
    protected $_distinctFlag = false;
    protected $_image_base_path;
    protected $_onlyBindUnboundProducts = false;
    protected $_setMainIfNoMainExist = false;
    protected $_orig_locations = array();
    protected $_boundCount = 0;

    const SKIP_SMALL_THUMB_IN_GALLERY = false; // true->NOK200: Don't use explicit small/thumbnail images in media gallery
    const SORTORDER_FIELD_PADDING = 30;

    // directory names to move on success/failure by _imgToFailureSuccessDir()
    const DIR_FAILURE = 'fail';
    const DIR_SUCCESS = 'success';

    public function __construct($param)
    {
        if (isset($param['silentf']) && $param['silentf']) {
            $this->_silent = true;
        }
        if ((Icommerce_Default::isEnterprise() && version_compare(Mage::getVersion(), '1.7', '<') === true) || version_compare(Mage::getVersion(), '1.4', '<') === true) {
            if (!$this->_silent) {
                echo '<b>ImageBind - Detected (older) Magento version ' . Mage::getVersion() . '...</b><br>' . "\n";
            }
            $this->_oldMagento = true;
        } else {
            if (!$this->_silent) {
                echo '<b>ImageBind - Detected (newer) Magento version 1.4 or later...</b><br>' . "\n";
            }
        }
        $this->_remove_before_import = ( isset($param['remove_before_import']) ?
                                         $param['remove_before_import'] :  
                                         Mage::getStoreConfig("imagebinder/settings/remove_before_import") );
        
        $read = Icommerce_Db::getDbRead();
        // Is flag for original ImageBinder mode set ?
        $orig_mode = (int) (isset($param["orig_mode"]) ? $param["orig_mode"] : Mage::getStoreConfig('imagebinder/settings/orig_mode'));
        $this->_image_base_path = (isset($param["image_base_path"]) ? $param["image_base_path"] : null);
        // If neither of orig_mode or explicit regex set, read from core_config_data
        $this->_regex = isset($param["regex"]) ? $param["regex"] : ($orig_mode ? "^([^_]+)(?:_(.+))?()$" : Mage::getStoreConfig('imagebinder/settings/regex'));
        $this->_attrField = $orig_mode ? 1 : (int)Mage::getStoreConfig('imagebinder/settings/attribute_field');
        $this->_sortField = $orig_mode ? 2 : (int)Mage::getStoreConfig('imagebinder/settings/sortorder_field');
        $this->_typeField = $orig_mode ? 2 : (int)Mage::getStoreConfig('imagebinder/settings/imagetype_field');
        if ($this->_typeField == '') {
            $this->_typeField = null;
        }
        $attrCode = $orig_mode ? "sku" : Mage::getStoreConfig('imagebinder/settings/attribute_code');
        $attrInfo = Icommerce_Eav::getAttributeInfo($attrCode, 'catalog_product');
        if (!$attrInfo) {
            throw new Exception(sprintf('Attribute %s does not exist', $attrCode));
        }
        $this->_valueTable = 'catalog_product_entity';
        $this->_whereTmpl = '';
        if (!empty($attrInfo['backend_type']) && $attrInfo['backend_type'] != 'static') {
            $this->_valueTable .= '_' . $attrInfo['backend_type'];
            $this->_whereTmpl = $read->quoteInto('attribute_id=?', array($attrInfo['attribute_id']));
            $this->_distinctFlag = true; //TODO: likely have to add current store to WHERE
            $attrCode = 'value';
        }
        $matchMode = $orig_mode ? Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_EXACT : Mage::getStoreConfig('imagebinder/settings/attribute_match_mode');
        switch ($matchMode) {
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_CONTAINS:
                $this->_whereTmpl .= ' AND ' . $attrCode . ' like ?';
                $this->_whereAttrFormat = '%%%s%%';
                break;
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_STARTSWITH:
                $this->_whereTmpl = $attrCode . ' like ?';
                $this->_whereAttrFormat = '%s%%';
                break;
            case Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_ENDSWITH:
                $this->_whereTmpl = $attrCode . ' like ?';
                $this->_whereAttrFormat = '%%%s';
                break;
            default: //Icommerce_ImageBinder_Model_Adminhtml_Source_Matchmode::MATCHMODE_EXACT:
                $this->_whereTmpl = $attrCode . '=?';
                $this->_whereAttrFormat = '%s';
                break;
        }
        $this->_sortDir = $orig_mode ? "expl" : Mage::getStoreConfig('imagebinder/settings/sortorder_type');
        if ($this->_sortField == '') {
            $this->_sortDir = null;
        }
        $this->_onlyBindUnboundProducts = $orig_mode ? false : (bool)Mage::getStoreConfig('imagebinder/settings/only_bind_unbound');
        $this->_setMainIfNoMainExist = $orig_mode ? false : (bool)Mage::getStoreConfig('imagebinder/settings/set_as_main_if_no_main_exist');
    }

    public function setSilentMode($mode = true)
    {
        $this->_silent = $mode;
    }

    public function getTargetImagePath($img)
    {
        if (!$this->_media_path) {
            $this->_media_path = Mage::getBaseDir() . '/media/catalog/product/';
        }
        $offset = $img[0] . '/' . $img[1] . '/';
        return $this->_media_path . $offset . $img;
    }

    // Function to import the image and produce the appropriate offset path+filename
    public function importMediaImage($path, $img)
    {
        if (!$this->_media_path) {
            $this->_media_path = Mage::getBaseDir() . '/media/catalog/product/';
        }
        $offset = $img[0] . '/' . $img[1] . '/';
        $tgt_path = $this->_media_path . $offset;
        if (!is_dir($tgt_path)) {
            $r = mkdir($tgt_path, 0777, true);
        }
        //$r = unlink( $tgt_path.$img );
        $r = copy($path . $img, $tgt_path . $img);
        return $offset . $img;
    }

    public function getImportBasePath()
    {
        if (!self::$SFTP_PATH) {
            // Extract instance
            $mv = array();
            preg_match('@.*/(\\w+)@', Mage::getBaseDir(), $mv);
            $ftp_path = 'file:///home/ftp/' . $mv[1] . '/';
            $path = $this->_image_base_path ? $this->_image_base_path : Mage::getStoreConfig('image_base_path', null);
            if (!$path) {
                if (is_dir($ftp_path . '/media/import')) {
                    $path = $ftp_path . '/media/import';
                } else {
                    $path = $ftp_path;
                }
            } else {
                if (!strstartswith($path, 'file://') && !strstartswith($path, '/')) {
                    // Assume relative offset from ftp location
                    $path = $ftp_path . $path;
                }
                if (is_dir($path . '/media/import')) {
                    $path .= '/media/import';
                }
            }
            if ($path[strlen($path) - 1] != '/') {
                $path .= '/';
            }
            self::$SFTP_PATH = $path;
        }
        return self::$SFTP_PATH;
    }

    private function _iterateFilesInDir($dir = null)
    {
        flush();
        @ob_flush();
        $n_file = 0;
        if ($handle = opendir($dir)) {
            // Collect our data here
            $prod_imgs = array();
            $prod_gallery = array();
            $prod_gallery_data = array();
            $this->_orig_locations = array();
            while (false !== ($file = readdir($handle))) {
                if ($file == '.' || $file == '..' || !is_file($dir . $file)) {
                    continue;
                }
                $mv = array();
                $is_image = preg_match('/(.*)\\.(png|gif|jpe?g)$/i', $file, $mv);
                if (!$is_image) {
                    continue;
                }
                $orig_file_name = $mv[1]; //without extension
                $sku = $mv[1];
                if (!preg_match('/' . $this->_regex . '/', $sku, $mv)) {
                    continue;
                }
                $mt = '';
                $sortorder = '';
                // Import the file
                $contents = $this->importMediaImage($dir, $file);
                $sku = $mv[$this->_attrField];
                if ($this->_sortDir) {
                    $sortorder = $this->_sortDir != "expl" ? str_pad($mv[$this->_sortField], self::SORTORDER_FIELD_PADDING, ' ', STR_PAD_LEFT) : (int)$mv[$this->_sortField];
                }
                if ($this->_typeField && isset($mv[$this->_typeField])) {
                    $mt = strtolower($mv[$this->_typeField]);
                }
                //if we don't sort on a specified field and get only digits (or blank), use it imagetype as sort key
                if ($this->_sortDir == null && preg_match('/^[0-9]*$/', $mt)) {
                    $sortorder = str_pad($mt, self::SORTORDER_FIELD_PADDING, ' ', STR_PAD_LEFT);
                    $mt = '';
                }
                if (!array_key_exists($sku, $prod_imgs)) {
                    $prod_imgs[$sku] = array('image' => '', 'small_image' => '', 'thumbnail' => '', 'image_label' => '', 'small_image_label' => '', 'thumbnail_label' => '',
                        'minref' => null);
                    $prod_gallery[$sku] = array();
                    $prod_gallery_data[$sku] = array();
                    $this->_orig_locations[$sku] = array();
                }
                $mia = &$prod_imgs[$sku];
                $pga = &$prod_gallery[$sku];
                $pgd = &$prod_gallery_data[$sku];
                $this->_orig_locations[$sku][] = array($dir, $file);
                $key = null;
                $sortcomp = true;
                if ($sortorder !== '') {
                    if ($mia['minref'] != null) {
                        switch ($this->_sortDir) {
                            case 'expl':
                                //$sortcomp = true;
                                break;
                            case 'desc':
                                $sortcomp = strcmp($sortorder, $mia['minref']) > 0;
                                break;
                            default: //asc
                                $sortcomp = strcmp($sortorder, $mia['minref']) < 0;
                                break;
                        }
                    }
                }
                $label = null;
                foreach (array('.label.txt', '.label.TXT', '.LABEL.txt', '.LABEL.TXT') as $suffix) {
                    $labelfile = $dir . $orig_file_name . $suffix;
                    if (file_exists($labelfile)) {
                        $label = trim(file_get_contents($labelfile));
                        $this->_orig_locations[$sku][] = array($dir, $orig_file_name . $suffix);
                        break;
                    }
                }
                if ($sortcomp) {
                    $mia['minref'] = $sortorder;
                    if ($mt == '') {
                        $key = 'image';
                    } elseif ($mt == 's' || $mt == 'st' || $mt == 'ts') {
                        $pos = -1;
                        $key = 'small_image';
                    } elseif ($mt == 't') {
                        $pos = -2;
                        $key = 'thumbnail';
                    } else {
                        // If sort and type field are same (original ImageBinder), then accept
                        if ($this->_typeField !== $this->_sortField) {
                            continue;
                        } // Unknown function for image
                    }
                    $mia[$key] = $contents;
                    if ($label) {
                        $mia[$key . '_label'] = $label;
                    }
                }
                // Store image into array
                if ($is_image) {
                    if (!self::SKIP_SMALL_THUMB_IN_GALLERY || ($key != 'small_image' && $key != 'thumbnail')) { //See note for SKIP_SMALL_THUMB_IN_GALLERY
                        if ($sortorder !== '') {
                            $pga[$sortorder] = $contents;
                            $pgd[$sortorder]['type'] = $key;
                            if ($label) {
                                $pgd[$sortorder]['label'] = $label;
                            }
                        } else {
                            $pga[$contents] = $contents;
                            $pgd[$contents]['type'] = $key;
                            if ($label) {
                                $pgd[$contents]['label'] = $label;
                            }
                        }
                    }
                }
                // Progress
                if (!(++$n_file % 100)) {
                    echo '<b>ImageBind - Iterating files - ' . $n_file . '</b><br>' . "\n";
                    flush();
                    @ob_flush();
                    Icommerce_Db::dbKeepAlive();
                }
            }
            closedir($handle);
            echo '<b>ImageBind - Finished iterating files.</b><br>' . "\n";
            flush();
            @ob_flush();
            return array('prod_imgs' => $prod_imgs, 'prod_gallery' => $prod_gallery, 'prod_gallery_data' => $prod_gallery_data,);
        }
        return null;
    }

    function getProductIdsByAttributeValue($attrval)
    {
        $read = Icommerce_Db::getDbRead();
        $select = $read->select();
        $select->from($this->_valueTable, array('entity_id'));
        $select->distinct($this->_distinctFlag);
        $select->where($read->quoteInto($this->_whereTmpl, array(sprintf($this->_whereAttrFormat, $attrval))));
        return $read->fetchCol($select);
    }

    function doInsertMultiple($table, $data, $conn)
    {
        if ($this->_oldMagento) {
            foreach ($data as $row) {
                $conn->insert($table, $row);
            }
        } else {
            $conn->insertMultiple($table, $data);
        }
    }

    protected function _productHasMainImages($entity_id)
    {
        $read = Icommerce_Db::getDbRead();
        $attrId = Icommerce_Eav::getAttributeId('image', 'catalog_product');
        $select = $read->select()->from('catalog_product_entity_varchar', array('entity_id', 'value'))->where('attribute_id=?', array($attrId))->where('store_id=0')
                ->where('value IS NOT NULL')->where('CHAR_LENGTH(TRIM(value))>0')->where('entity_id=?', array($entity_id));
        $idarr = $read->fetchRow($select);
        if (!idarr) return false;
        if ($idarr['value']=='no_selection') return false;
        return ((int)$idarr['entity_id'] > 0);
    }

    protected function _productHasGalleryImages($entity_id)
    {
        $read = Icommerce_Db::getDbRead();
        $select = $read->select()->from('catalog_product_entity_media_gallery', array('entity_id'))->where('entity_id=?', array($entity_id));
        $id = $read->fetchOne($select);
        return ((int)$id > 0);
    }

    protected function _filterOutProductsWithMainImages(&$productIds)
    {
        foreach ($productIds as $idx => $pid) {
            if ($this->_productHasMainImages($pid)) {
                unset($productIds[$idx]);
            }
        }
    }

    protected function _filterOutProductsWithGalleryImages(&$productIds)
    {
        foreach ($productIds as $idx => $pid) {
            if ($this->_productHasGalleryImages($pid)) {
                unset($productIds[$idx]);
            }
        }
    }

    public function insertImages($prod_imgs, $prod_gallery = null, $prod_gallery_data = null)
    {
        // Load and change data
        $productmodel = Mage::getModel('catalog/product');
        if ($this->_oldMagento) {
            $productAction = new M14ProductAction(); //special work_around class mimicking M1.4's catalog/product_action class
        } else {
            $productAction = Mage::getSingleton('catalog/product_action'); //proper catalog/product_action class
        }
        // Go through $prod_imgs and write them to the database
        if (!$this->_silent) {
            echo '<b>ImageBind - Saving product images...</b><br>' . "\n";
            flush();
            @ob_flush();
        }
        $n_file = 0;
        if (!$this->_setMainIfNoMainExist) { // Don't run this code if that option is active.
        foreach ($prod_imgs as $sku => &$pi) {
            $productIds = $this->getProductIdsByAttributeValue($sku);
            if ($this->_onlyBindUnboundProducts) {
                $this->_filterOutProductsWithMainImages($productIds);
            }
            if (empty($productIds)) {
                $this->_imgToFailureSuccessDir($sku);
                continue;
            } else {
                $this->_imgToFailureSuccessDir($sku, true);
            }
            // For default image, move it to small and thumbnail, if still empty
            if ($pi['small_image'] == '') {
                $pi['small_image'] = $pi['image'];
            }
            if ($pi['thumbnail'] == '') {
                $pi['thumbnail'] = $pi['small_image'];
            }
            // Same for the image labels
            if ($pi['small_image_label'] == '') {
                $pi['small_image_label'] = $pi['image_label'];
            }
            if ($pi['thumbnail_label'] == '') {
                $pi['thumbnail_label'] = $pi['small_image_label'];
            }
            unset($pi['minref']);
            unset($pi['']);
            /**
             * @var Mage_Catalog_Model_Resource_Product_Action
             */
            $productAction->updateAttributes($productIds, $pi, 0);
            $this->_boundCount++;
            // Progress
            if (!(++$n_file % 100) && !$this->_silent) {
                echo '<b>ImageBind - Saving products - ' . $n_file . '</b><br>' . "\n";
                flush();
                @ob_flush();
            }
        }
        if (!$this->_silent) {
            echo '<b>ImageBind - Saving product images completed.</b><br>' . "\n";
            flush();
            @ob_flush();
        }
        }
        // Write media gallery directly to DB table
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $id_media_gallery = Icommerce_Eav::getAttributeId("media_gallery", "catalog_product");
        if (!isset($id_media_gallery) || empty($id_media_gallery)) {
            throw new Exception('Failed looking up media_gallery attribute ID');
        }
        if (!$this->_silent) {
            echo '<b>ImageBind - Inserting into gallery database...</b><br>' . "\n";
            flush();
            @ob_flush();
        }
        $n_file = 0;
        foreach ($prod_gallery as $sku => &$pg) {
            $productIds = $this->getProductIdsByAttributeValue($sku);
            if ($this->_onlyBindUnboundProducts) {
                $this->_filterOutProductsWithGalleryImages($productIds);
            }
            if (!empty($productIds)) {
                if( $this->_remove_before_import ){
                    if ($this->_remove_before_import!=2 || $this->_sortDir!="expl") {
                        $condition = $write->quoteInto('entity_id IN (?)', $productIds);
                        $write->delete('catalog_product_entity_media_gallery', $condition);
                    }
                }

                // Insert images - if not explicit gallery position, sort by index/key!
                switch ($this->_sortDir) {
                    case 'expl':
                        break;
                    case 'desc':
                        krsort($pg);
                        break;
                    default: //'asc':
                        ksort($pg);
                        break;
                }
                $pos = 0;
                $gvalsz = 0;
                foreach ($pg as $org_pos => $img) {
                    foreach ($productIds as $id) {
                        $sort_id = $this->_sortDir == "expl" ? $org_pos : $pos;
                        
                        if ($this->_remove_before_import==2 && $this->_sortDir=="expl") {
                            $read = Icommerce_Db::getDbRead();
                            $select = $read->select()
                                ->from(array('gallery'=>'catalog_product_entity_media_gallery'), array('value_id'))
                                ->joinLeft(array('value'=>'catalog_product_entity_media_gallery_value'),'gallery.value_id = value.value_id')
                                ->where('gallery.entity_id=?', $id)
                                ->where('value.position=?',$sort_id);
                            $value_id = $read->fetchOne($select);
                            if ($value_id) {
                                $condition = $write->quoteInto('value_id=?', $value_id);
                                $write->delete('catalog_product_entity_media_gallery', $condition);
                            }
                        }

                        $write->insert('catalog_product_entity_media_gallery', array('attribute_id' => $id_media_gallery, 'entity_id' => $id, 'value' => $img));
                        $value_id = $write->lastInsertId('catalog_product_entity_media_gallery', 'value_id');
                        $label = isset($prod_gallery_data[$sku][$org_pos]['label']) ? $prod_gallery_data[$sku][$org_pos]['label'] : '';
                        $type = isset($prod_gallery_data[$sku][$org_pos]['type']) ? $prod_gallery_data[$sku][$org_pos]['type'] : '';
                        $disabled = ($type == 'small_image' || $type == 'thumbnail_image') ? 1 : 0;
                        $gvals[] = array('value_id' => $value_id, 'label' => $label, 'position' => $sort_id, 'disabled' => $disabled,);
                        if (!(++$gvalsz % 100)) {
                            $this->doInsertMultiple('catalog_product_entity_media_gallery_value', $gvals, $write);
                            unset($gvals);
                            $gvalsz = 0;
                        }
                    }
                    $pos++;
                    // Progress
                    if (!(++$n_file % 100) && !$this->_silent) {
                        echo '<b>ImageBind - Insert into gallery database - ' . $n_file . '</b><br>' . "\n";
                        flush();
                        @ob_flush();
                    }
                }
                if (isset($gvals)) {
                    $this->doInsertMultiple('catalog_product_entity_media_gallery_value', $gvals, $write);
                    unset($gvals);
                }
            }
        }
        if ($this->_setMainIfNoMainExist) {
            foreach ($prod_gallery as $sku => &$pg) {
                $productIds = $this->getProductIdsByAttributeValue($sku);
                $this->_filterOutProductsWithMainImages($productIds);
                if (!empty($productIds)) {
                    foreach ($productIds as $id) {
                        $read = Icommerce_Db::getDbRead();
                        $select = $read->select()
                            ->from(array('gallery'=>'catalog_product_entity_media_gallery'), array('value'))
                            ->joinLeft(array('value'=>'catalog_product_entity_media_gallery_value'),'gallery.value_id = value.value_id')
                            ->where('gallery.entity_id=?', $id)
                            ->order('value.position ASC')
                            ->limit(1);
                        $value_name = $read->fetchOne($select);
                        $img_attr = array();
                        $img_attr['image'] = $value_name;
                        $img_attr['small_image'] = $value_name;
                        $img_attr['thumbnail'] = $value_name;
                        $img_attr['small_image_label'] = '';
                        $img_attr['thumbnail_label'] = '';
                        $productAction->updateAttributes($productIds, $img_attr, 0);
                    }
                }
            }
        }
        if (!$this->_silent) {
            echo '<b>ImageBind - Inserting into gallery database completed.</b><br>' . "\n";
            flush();
            @ob_flush();
        }
        unset($productmodel); //should not be needed!!
    }

    private function _imgToFailureSuccessDir($sku, $is_success = false)
    {
        static $move_active = null;
        if (null === $move_active) {
            $move_active = (bool )Mage::getStoreConfig('imagebinder/settings/move_failsuccess_path');
        }
        if ($move_active !== true || empty($this->_orig_locations[$sku])) {
            return false;
        }
        $files_data = $this->_orig_locations[$sku];
        foreach ($files_data as $file_data) {
            $dir_to = $is_success ? self::DIR_SUCCESS : self::DIR_FAILURE;
            $dir_exists = file_exists($file_data[0] . $dir_to);
            if (!$dir_exists) {
                if ($this->_isDirWritableCached($file_data[0])) {
                    $dir_exists = mkdir($file_data[0] . $dir_to);
                } else {
                    error_log(__METHOD__ . '() - path not writable (' . $file_data[0] . ')');
                }
            } elseif (!$this->_isDirWritableCached($file_data[0] . $dir_to)) {
                error_log(__METHOD__ . '() - path not writable (' . $file_data[0] . $dir_to . ')');
                return false;
            }
            if ($dir_exists && file_exists($file_data[0] . $file_data[1])) {
                if (copy($file_data[0] . $file_data[1], $file_data[0] . $dir_to . '/' . $file_data[1])) {
                    unlink($file_data[0] . $file_data[1]);
                } else {
                    error_log(__METHOD__ . '() -  copy unsuccessful to (' . $file_data[0] . $dir_to . '/' . $file_data[1] . ')');
                }
            }
        }
        unset($this->_orig_locations[$sku]);
        return true;
    }

    private function _isDirWritableCached($dir)
    {
        static $is_writable_cache = array();
        if (array_key_exists($dir, $is_writable_cache)) {
            return $is_writable_cache[$dir];
        }
        // check if path is writeable and executable (wx)
        $is_writable_cache[$dir] = is_writable($dir) && is_executable($dir);
        return $is_writable_cache[$dir];
    }

    public function bindAll($observer = null)
    {
        echo 'ImageBinder::bindAll <br>' . "\n";
        flush();
        @ob_flush();
        if (self::$st_did_import) {
            return;
        }
        self::$st_did_import = true;
        // Iterate over all images in directory
        try {
            $dir = $this->getImportBasePath();
            echo 'Image bind base dir:' . $dir . '<br>' . "\n";
            $res = $this->_iterateFilesInDir($dir); //returns an array containing prod_imgs and prod_gallery
            $images = array('prod_imgs' => &$res['prod_imgs'], 'prod_gallery' => &$res['prod_gallery'], 'prod_gallery_data' => &$res['prod_gallery_data'],);
            Mage::dispatchEvent('imagebinder_after_iterate_files', array('images' => $images));
            if ($res) {
                $prod_imgs = &$res['prod_imgs'];
                $prod_gallery = &$res['prod_gallery'];
                $prod_gallery_data = &$res['prod_gallery_data'];
                $this->insertImages($prod_imgs, $prod_gallery, $prod_gallery_data);
                echo '<b>ImageBind - finished: OK!</b><br>' . "\n";
                flush();
                @ob_flush();
            }
            Mage::dispatchEvent('imagebinder_after_insert_images', array('images' => $images));
        } catch (Exception $e) {
            print_r($e);
        }

        return $this->_boundCount;
    }

    public function onDefault($observer)
    {
        //echo 'ImageBinder::onDefault <br>' . "\n";
        //die();
        $a = 1;
    }
}

/**
 * The following class is provided only for backward compatibility with Magento versions prior to 1.4
 * The code is mostly copied from Magento 1.4 and adapted as needed to make the class as short as possible
 * //PLEASE NOTE: THIS SPECIAL ADAPTATION DOES NOT UPDATE ANY INDEXES!!!
 * (Its good enough for ImageBinder though, because in M1.3 at least,
 * none of the attributes we are updating are indexed or searchable)
 */
class M14ProductAction extends Mage_Eav_Model_Entity_Abstract
{
    /**
     * Entity attribute values per backend table to delete
     *
     * @var array
     */
    protected $_attributeValuesToDelete = array();

    /**
     * Entity attribute values per backend table to save
     *
     * @var array
     */
    protected $_attributeValuesToSave = array();

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('catalog_product');
        $this->setConnection($resource->getConnection('catalog_read'), $resource->getConnection('catalog_write'));
    }

    /**
     * Redeclare attribute model
     *
     * @return string
     */
    protected function _getDefaultAttributeModel()
    {
        return 'catalog/resource_eav_attribute';
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int   $storeId
     * @return Mage_Catalog_Model_Product_Action
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        $object = new Varien_Object();
        $object->setIdFieldName('entity_id')->setStoreId($storeId);
        $this->_getWriteAdapter()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {
                $attribute = $this->getAttribute($attrCode);
                if (!$attribute->getAttributeId()) {
                    continue;
                }
                $i = 0;
                foreach ($entityIds as $entityId) {
                    $object->setId($entityId);
                    // collect data for save
                    $this->_saveAttributeValue($object, $attribute, $value);
                    // save collected data every 1000 rows
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                }
            }
            $this->_processAttributeValues();
            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Insert or Update attribute data
     *
     * @param Mage_Catalog_Model_Abstract              $object
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed                                    $value
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Abstract
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $write = $this->_getWriteAdapter();
        $storeId = Mage::app()->getStore($object->getStoreId())->getId();
        $table = $attribute->getBackend()->getTable();
        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        if (Mage::app()->isSingleStoreMode()) {
            $storeId = $this->getDefaultStoreId();
            $write->delete($table, join(' AND ', array($write->quoteInto('attribute_id=?', $attribute->getAttributeId()), $write->quoteInto('entity_id=?', $object->getEntityId()),
                $write->quoteInto('store_id<>?', $storeId))));
        }
        $bind = array('entity_type_id' => $attribute->getEntityTypeId(), 'attribute_id' => $attribute->getAttributeId(), 'store_id' => $storeId,
            'entity_id' => $object->getEntityId(), 'value' => $this->_prepareValueForSave($value, $attribute));
        if ($attribute->isScopeStore()) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$table][] = $bind;
        } else {
            if ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
                /**
                 * Update attribute value for website
                 */
                $storeIds = Mage::app()->getStore($storeId)->getWebsite()->getStoreIds(true);
                foreach ($storeIds as $storeId) {
                    $bind['store_id'] = $storeId;
                    $this->_attributeValuesToSave[$table][] = $bind;
                }
            } else {
                /**
                 * Update global attribute value
                 */
                $bind['store_id'] = $this->getDefaultStoreId();
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        }
        return $this;
    }

    /**
     * Prepare value for save
     *
     * @param mixed                                    $value
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return mixed
     */
    protected function _prepareValueForSave($value, Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        $type = $attribute->getBackendType();
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            return null;
        }
        if ($type == 'decimal') {
            return Mage::app()->getLocale()->getNumber($value);
        }
        return $value;
    }

    /**
     * Save and detele collected attribute values
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _processAttributeValues()
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($this->_attributeValuesToSave as $table => $data) {
            $this->insertOnDuplicate($adapter, $table, $data, array('value'));
        }
        foreach ($this->_attributeValuesToDelete as $table => $valueIds) {
            $adapter->delete($table, array('value_id IN(?)' => $valueIds));
        }
        // reset data arrays
        $this->_attributeValuesToSave = array();
        $this->_attributeValuesToDelete = array();
        return $this;
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table  The table to insert data into.
     * @param array $data   Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     */
    public function insertOnDuplicate($adapter, $table, array $data, array $fields = array())
    {
        // extract and quote col names from the array keys
        $row = reset($data); // get first elemnt from data array
        $bind = array(); // SQL bind array
        $cols = array();
        $values = array();
        if (is_array($row)) { // Array of column-value pairs
            $cols = array_keys($row);
            foreach ($data as $row) {
                $line = array();
                if (array_diff($cols, array_keys($row))) {
                    throw new Varien_Exception('Invalid data for insert');
                }
                foreach ($row as $val) {
                    if ($val instanceof Zend_Db_Expr) {
                        $line[] = $val->__toString();
                    } else {
                        $line[] = '?';
                        $bind[] = $val;
                    }
                }
                $values[] = sprintf('(%s)', join(',', $line));
            }
            unset($row);
        } else { // Column-value pairs
            $cols = array_keys($data);
            $line = array();
            foreach ($data as $val) {
                if ($val instanceof Zend_Db_Expr) {
                    $line[] = $val->__toString();
                } else {
                    $line[] = '?';
                    $bind[] = $val;
                }
            }
            $values[] = sprintf('(%s)', join(',', $line));
        }
        $updateFields = array();
        if (empty($fields)) {
            $fields = $cols;
        }
        // quote column names
        $cols = array_map(array($adapter, 'quoteIdentifier'), $cols);
        // prepare ON DUPLICATE KEY conditions
        foreach ($fields as $k => $v) {
            $field = $value = null;
            if (!is_numeric($k)) {
                $field = $adapter->quoteIdentifier($k);
                if ($v instanceof Zend_Db_Expr) {
                    $value = $v->__toString();
                } else {
                    if (is_string($v)) {
                        $value = 'VALUES(' . $adapter->quoteIdentifier($v) . ')';
                    } else {
                        if (is_numeric($v)) {
                            $value = $adapter->quoteInto('?', $v);
                        }
                    }
                }
            } else {
                if (is_string($v)) {
                    $field = $adapter->quoteIdentifier($v);
                    $value = 'VALUES(' . $field . ')';
                }
            }
            if ($field && $value) {
                $updateFields[] = "{$field}={$value}";
            }
        }
        // build the statement
        $sql = 'INSERT INTO ' . $adapter->quoteIdentifier($table, true) . ' (' . implode(', ', $cols) . ') ' . 'VALUES ' . implode(', ', $values);
        if ($updateFields) {
            $sql .= ' ON DUPLICATE KEY UPDATE ' . join(', ', $updateFields);
        }
        // execute the statement and return the number of affected rows
        $stmt = $adapter->query($sql, array_values($bind));
        $result = $stmt->rowCount();
        return $result;
    }
}
