<?php
/**
 * Vaimo_PrevNextLocal use this file to always get a cached thumbnail image
 * This file return the thumbnail image
 * If the thumbnail image do not exist then it scale the big image and cache it, then return the thumbnail image.
 */
error_reporting(E_ALL | E_STRICT);

class imageManager {

    private function getConfig() {
        define('DS', DIRECTORY_SEPARATOR);
        $fileName = realpath(dirname(__FILE__)) . DS . 'config.json';
        if (file_exists($fileName) === true) {
            $data = file_get_contents($fileName);
            $data = json_decode($data, true);
            return $data;
        }
        $data = array(
            'base'=>array(
                'image_dir' => '',
                'cache_path' => ''
            )
        );
        return $data;
    }

    private function getImageDirs($baseDir = '', $cachePath = '', $imageName = '') {
        $out = array('image_dir' => '', 'cached_image_dir' => '');
        if (empty($baseDir) or empty($imageName)) {
            goto leave;
        }

        $baseDir = $this->urlTrim($baseDir, '/', '/');
        $catalogProduct = 'catalog/product/';
        $imageName = $this->urlTrim($imageName, '', '');
        $out['image_dir'] = $baseDir . $catalogProduct . $imageName;
        if (empty($cachePath)) {
            $out['cached_image_dir'] = $out['image_dir'];
        }

        $cachePath = $this->urlTrim($cachePath, '', '/');
        $out['cached_image_dir'] = $baseDir . $catalogProduct . $cachePath . $imageName;

        leave:
        return $out;
    }

    private function urlTrim($row, $begin, $end) {
        $row = trim($row);
        if (empty($row)) {
            return $row;
        }
        if ($begin === '' and $row[0] === '/') {
            $row = substr($row,1); // Remove first /
        }
        if ($begin === '/' and $row[0] !== '/') {
            $row = '/' . $row;
        }
        if ($end === '' and $row[strlen($row)-1] === '/') {
            $row = substr($row,0,-1); // Remove last /
        }
        if ($end === '/' and $row[strlen($row)-1] !== '/') {
            $row = $row . '/';
        }
        return $row;
    }

    public function getImage() {
        $config = $this->getConfig();
        $imageName = $this->getParam('image');
        if (empty($imageName)) {
            return;
        }

        $baseDir = $config['base']['image_dir'];
        $cachePath = $config['base']['cache_path'];
        $imageDirs = $this->getImageDirs($baseDir, $cachePath, $imageName);
        $this->sendImageAndExit($imageDirs['cached_image_dir']);

        // Since we are still here we did not get the cached image.
        // Scale down and save a cached image and try again

        $this->scaleAndCacheImage($imageDirs);
        $this->sendImageAndExit($imageDirs['cached_image_dir']);

        // Since we are still here we could not scale the image, we just send it as it is
        $this->sendImageAndExit($imageDirs['image_dir']);
    }

    private function sendImageAndExit($fileName) {
        $exist = file_exists($fileName);
        if ($exist === false) {
            return false;
        }
        $fp = fopen($fileName, 'rb');
        $size = getimagesize($fileName);
        header("Content-type: {$size['mime']}");
        header("Content-Length: " . filesize($fileName));
        fpassthru($fp);
        exit(0);
    }

    private function getParam($name) {
        try {
            $data = $_GET[$name];
        } catch (Exception $e) {

        }
        if (empty($data)) {
            $data = '';
        }
        return $data;
    }

    private function scaleAndCacheImage($imageDirs, $width = 100, $height = 100) {
        $sourceFileName = $imageDirs['image_dir'];
        $destinationFileName = $imageDirs['cached_image_dir'];
        list($sourceWidth, $sourceHeight) = getimagesize($sourceFileName);
        $sourceRatio = $sourceWidth/$sourceHeight;
        if ($width / $height > $sourceRatio) {
            $width = $height * $sourceRatio;
        } else {
            $height = $width / $sourceRatio;
        }
        $destinationImage = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg($sourceFileName);
        imagecopyresampled($destinationImage, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        $paths = pathinfo($destinationFileName);
        mkdir($paths['dirname'],0777,true); // Recurively create all missing folders

        // Save the new image
        $response = imagejpeg($destinationImage, $destinationFileName, 100);
        return $response;
    }

}

$image = new imageManager();
$image->getImage();
