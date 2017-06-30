<?php

class MagicToolbox_Magic360_Model_Observer {

    public function __construct() {

    }

    public function checkForMagic360Product($observer) {
        $helper = Mage::helper('magic360/settings');
        if($helper->isModuleOutputEnabled()) {
            $id = $observer->getEvent()->getProduct()->getId();
            $id = (int)$id;//NOTE: just in case (if $id will be empty)
            //$gallery = $product->getMediaGalleryImages();
            //$imagesCount = $gallery->getSize();
            ////NOTE: for old Magento ver. 1.3.x
            //if(is_null($imagesCount)) {
            //    $imagesCount = count($gallery->getItems());
            //}
            $tool = $helper->loadTool('product');
            $images = array();
            $imagesCount = 0;
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            $table = $resource->getTableName('magic360/gallery');
            $result = $connection->query("SELECT columns, gallery FROM {$table} WHERE product_id = {$id}");
            if($result) {
                $rows = $result->fetch(PDO::FETCH_ASSOC);
                if($rows) {
                    $_images = Mage::helper('core')->jsonDecode($rows['gallery']);
                    foreach($_images as $image) {
                        if($image['disabled']) continue;
                        $images[] = array(
                            'url' => $image['url'],
                            'file' => $image['file']
                        );
                    }
                    $imagesCount = count($images);
                    $columns = $rows['columns'] > $imagesCount ? $imagesCount : $rows['columns'];
                    $tool->params->setValue('columns', $columns, $tool->params->generalProfile);
                    $tool->params->setValue('columns', $columns, 'product');
                }
            }
            if($imagesCount) {
                Mage::register('magic360ClassName', 'magic360');
                Mage::register('magic360Images', $images);
            } else {
                Mage::register('magic360ClassName', false);
            }
        }
    }

    public function checkUploaderClass($observer) {
        $useNewUploaderClass = false;
        $galleryBlock = $observer->getEvent()->getBlock();
        if($galleryBlock) {
            $uploader = $galleryBlock->getUploader();
            if($uploader && get_class($uploader) == 'Mage_Uploader_Block_Multiple') {
                $useNewUploaderClass = true;
            }
        }
        Mage::register('magic360NewUploaderClass', $useNewUploaderClass);
    }

    /* NOTE: after get layout updates */
    public function fixLayoutUpdates($observer) {
        //NOTE: to prevent an override of our templates with other modules
        //NOTE: also to sort the modules layout for displaying headers in the right order

        global $isLayoutUpdatesAlreadyFixed;
        if(isset($isLayoutUpdatesAlreadyFixed)) return;
        $isLayoutUpdatesAlreadyFixed = true;

        //$xml = Mage::app()->getConfig()->getNode('frontend/layout/updates')->asNiceXml();
        //debug_log($xml);

        //NOTE: default order (without sorting)
        //Magic360
        //MagicScroll
        //MagicSlideshow
        //MagicThumb
        //MagicZoom
        //MagicZoomPlus

        //NOTE: sort order
        $modules = array(
            'magic360' => false,
            'magicthumb' => false,
            'magiczoom' => false,
            'magiczoomplus' => false,
            'magicscroll' => false,
            'magicslideshow' => false,
        );

        $pattern = '#^(?:'.implode('|', array_keys($modules)).')$#';
        foreach(Mage::app()->getConfig()->getNode('frontend/layout/updates')->children() as $key => $child) {
            if(preg_match($pattern, $key)) {
                //NOTE: remember detected modules 
                $modules[$key] = array(
                    'module' => $child->getAttribute('module'),
                    'file' => (string)$child->file,
                );
            }
        }

        //NOTE: remove node to prevent dublicate
        $path = implode(' | ', array_keys($modules));
        $elements = Mage::app()->getConfig()->getNode('frontend/layout/updates')->xpath($path);
        foreach($elements as $element) {
            unset($element->{0});
        }

        //NOTE: add new nodes to the end
        foreach($modules as $key => $data) {
            if(empty($data)) continue;
            $child = new Varien_Simplexml_Element("<{$key} module=\"{$data['module']}\"><file>{$data['file']}</file></{$key}>");
            Mage::app()->getConfig()->getNode('frontend/layout/updates')->appendChild($child);
        }

    }

    /* NOTE: before generate layout xml */
    public function addLayoutUpdate($observer) {

        global $isLayoutUpdateAlreadyAdded;
        if(isset($isLayoutUpdateAlreadyAdded)) return;
        $isLayoutUpdateAlreadyAdded = true;

        $layout = $observer->getEvent()->getLayout();
        //NOTE: modules are already sorted by order (fixLayoutUpdates)
        $pattern = '#^magic(?:thumb|360|zoom|zoomplus|scroll|slideshow)$#';
        foreach(Mage::app()->getConfig()->getNode('frontend/layout/updates')->children() as $key => $child) {
            if(preg_match($pattern, $key, $match)) {
                //NOTE: add layout update for detected module
                $xml = '
<reference name="product.info.media">
    <action method="setTemplate">
        <template helper="'.$match[0].'/settings/getBlockTemplate">
            <name>product.info.media</name>
            <template>'.$match[0].'/media.phtml</template>
        </template>
    </action>
</reference>';
                $layout->getUpdate()->addUpdate($xml);
            }
        }
    }

    public function saveProductImagesData($observer) {
        try {
            $data = Mage::app()->getRequest()->getPost('magic360');
            if($data) {
                $images = Mage::helper('core')->jsonDecode($data['gallery']);
                $images_to_save = array();
                $columns = 0;
                foreach($images as &$image) {
                    if($image['removed']) {
                        $file = str_replace('/', DS, $image['file']);
                        if(substr($file, 0, 1) == DS) {
                            $file = substr($file, 1);
                        }
                        $file = Mage::getBaseDir('media').DS.'magictoolbox'.DS.'magic360'.DS.$file;
                        @unlink($file);
                    } else {
                        $images_to_save[] = $image;
                        if(!$image['disabled']) {
                            $columns++;
                        }
                    }
                }
                if(!empty($data['columns']) && $data['columns'] < $columns) {
                    $columns = $data['columns'];
                }
                $compare = create_function('$a,$b', 'if($a["position"] == $b["position"]) return 0; return (int)$a["position"] > (int)$b["position"] ? 1 : -1;');
                usort($images_to_save, $compare);
                $data = Mage::helper('core')->jsonEncode($images_to_save);

                $lengthLimit = 5000;
                $dataParts = array();
                $dataLength = strlen($data);
                while($dataLength > $lengthLimit) {
                    $dataPart = substr($data, 0, $lengthLimit);

                    //NOTE: fixed an issue with bad SQL query
                    if($dataPart[strlen($dataPart)-1] == '\\') {
                        $lengthLimit--;
                        continue;
                    }

                    $data = substr($data, $lengthLimit);
                    $dataLength = strlen($data);
                    $dataParts[] = $dataPart;
                }
                $dataParts[] = $data;

                $id = $observer->getEvent()->getProduct()->getId();
                $resource = Mage::getSingleton('core/resource');
                $connection = $resource->getConnection('core_write');
                $table = $resource->getTableName('magic360/gallery');
                $result = $connection->query("SELECT product_id FROM {$table} WHERE product_id = {$id}");
                if($result) {
                    $rows = $result->fetch(PDO::FETCH_ASSOC);
                    if($rows) {
                        if(empty($images_to_save)) {
                            $connection->query("DELETE FROM {$table} WHERE product_id = {$id}");
                        } else {
                            //$connection->query("UPDATE {$table} SET columns = {$columns}, gallery = '{$data}' WHERE product_id = {$id}");
                            $query = "UPDATE {$table} SET columns = {$columns}, gallery = '{$dataParts[0]}' WHERE product_id = {$id}";
                            $connection->query($query);
                            unset($dataParts[0]);
                            if(count($dataParts)) {
                                foreach($dataParts as $dataPart) {
                                    $query = "UPDATE {$table} SET gallery = concat(gallery, '{$dataPart}') WHERE product_id = {$id}";
                                    $connection->query($query);
                                }
                            }
                        }
                    } else {
                        if(!empty($images_to_save)) {
                            //$connection->query("INSERT INTO {$table} (product_id, columns, gallery) VALUES ({$id}, {$columns}, '{$data}')");
                            $query = "INSERT INTO {$table} (product_id, columns, gallery) VALUES ({$id}, {$columns}, '{$dataParts[0]}')";
                            $connection->query($query);
                            unset($dataParts[0]);
                            if(count($dataParts)) {
                                foreach($dataParts as $dataPart) {
                                    $query = "UPDATE {$table} SET gallery = concat(gallery, '{$dataPart}') WHERE product_id = {$id}";
                                    $connection->query($query);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}
