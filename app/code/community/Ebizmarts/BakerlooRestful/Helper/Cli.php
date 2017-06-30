<?php

class Ebizmarts_BakerlooRestful_Helper_Cli extends Mage_Core_Helper_Abstract {

    public function getPathToDb($subdirName1, $subdirName2, $delete = false) {
        $path = Mage::getBaseDir('var') . DS . 'pos';

        $path .= DS . $subdirName1;
        $path .= DS . $subdirName2;

        $io = new Varien_Io_File();

        if($delete && $io->fileExists($path, false)) {
            $io->rmdirRecursive($path, true);
        }

        $io->setAllowCreateFolders(true)
           ->createDestinationDir($path);

        return $path;
    }

}