<?php
namespace Ebizmarts\BakerlooBackup;


interface BackupHandler {
    public function getAllFiles($dir = null);
    public function getFile($fileName, $storeId, $dir = null);
    public function uploadFile($file, $storeId, $dir = null);

}