<?php
/**
 * Created by JetBrains PhpStorm.
 * User: arne
 * Date: 2012-04-10
 * Time: 12.52
 * To change this template use File | Settings | File Templates.
 */

class Vaimo_AutoCleanMergedFiles_Model_Package extends Mage_Core_Model_Design_Package {


    public function addSemicolonSeparator($file, $contents)
    {
        return ';' . $contents;
    }

    /**
     * Overwrite original method in order to add filemtime as parameter
     *
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        $storeId = Mage::app()->getStore()->getId();
    	$cnt = $this->getCounter("js");
        $targetFilename = md5(implode(',', $files)) . '.' . $storeId . '.' . $cnt . '.js';
        $targetDir = $this->_initMergerDir('js');

        if (!$targetDir) {
            return '';
        }
        if (Mage::helper('core')->mergeFiles($files, $targetDir . DS . $targetFilename, false, array($this, 'addSemicolonSeparator'), 'js')) {
            return Mage::getBaseUrl('media') . 'js/' . $targetFilename;
        }
        return '';
    }

    /**
	 * Overwrite original method in order to add filemtime as parameter
	 *
	 * @return string
	 */
     public function getMergedCssUrl($files)
     {
         $storeId  = Mage::app()->getStore()->getId();
         // secure or unsecure
         $isSecure = Mage::app()->getRequest()->isSecure();
         $mergerDir = $isSecure ? 'css_secure' : 'css';
         $targetDir = $this->_initMergerDir($mergerDir);
         if (!$targetDir) {
             return '';
         }

         $cnt = $this->getCounter("css");
         $targetFilename = md5(implode(',', $files)) . '.' . $storeId . '.' . $cnt . '.css';

         if (Mage::helper('core')->mergeFiles($files, $targetDir . DS . $targetFilename, false, array($this, 'beforeMergeCss'), 'css')) {
             return Mage::getBaseUrl('media') . $mergerDir . '/' . $targetFilename;
         }
         return '';
     }

    protected function getCounter( $type ){
        $cnt = @file_get_contents( "var/count_" . $type ."_merge" );
        return $cnt ? (int)$cnt : 0;
    }

}

