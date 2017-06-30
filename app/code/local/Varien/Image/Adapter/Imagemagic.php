<?php
/**
 * Copyright (c) 2009-2012 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Imagick
 * @copyright   Copyright (c) 2009-2012 Icommerce Nordic AB
 */

/**
 * @file        Imagemagick.php
 */

class Varien_Image_Adapter_Imagemagic extends Varien_Image_Adapter_Abstract
{

    /**
     * @var $_imageHandler Imagick
     */
    protected $_imageHandler = null;
    protected $_resized = false;
    protected $_inputType = null;

    /**
     * Open image
     *
     * @param $fileName
     *
     * @throws Exception
     */
    public function open($fileName)
    {
        $this->_fileName = $fileName;
        $this->getMimeType();
        $this->_getFileAttributes();
        $original_image = new Imagick();
        if (!$original_image->readimage($this->_fileName)) {
            throw new Exception('Unable to open image by Imagick - ' . $this->_fileName);
        }

        // Place a possibly transparent image on top of white layer
        $this->_imageHandler = new IMagick();
        $this->_imageHandler->newImage(
            $original_image->getImageWidth(),
            $original_image->getImageHeight(),
            new ImagickPixel("white")
        );
        $this->_imageHandler->compositeImage($original_image, imagick::COMPOSITE_OVER, 0, 0);
    }

    /**
     * Save image
     *
     * @param null $destination
     * @param null $newName
     *
     * @throws Exception
     * @throws ImagickException
     */
    public function save($destination = null, $newName = null)
    {
        $fileName = (!isset($destination)) ? $this->_fileName : $destination;

        if (isset($destination) && isset($newName)) {
            $fileName = $destination . "/" . $newName;
        } elseif (isset($destination) && !isset($newName)) {
            $info = pathinfo($destination);
            $fileName = $destination;
            $destination = $info['dirname'];
        } elseif (!isset($destination) && isset($newName)) {
            $fileName = $this->_fileSrcPath . "/" . $newName;
        } else {
            $fileName = $this->_fileSrcPath . $this->_fileSrcName;
        }

        $destinationDir = (isset($destination)) ? $destination : $this->_fileSrcPath;

        if (!is_writable($destinationDir)) {
            try {
                $io = new Varien_Io_File();
                $io->mkdir($destination);
            } catch (Exception $e) {
                throw new Exception("Unable to write file into directory '{$destinationDir}'. Access forbidden.");
            }
        }

        if (!is_null($this->quality())) {
            $this->_imageHandler->setImageCompressionQuality(95);
        }

        $this->_imageHandler->writeImage($fileName);
    }

    /**
     * Change the image size
     *
     * @param int $frameWidth
     * @param int $frameHeight
     */
    public function resize($frameWidth = null, $frameHeight = null)
    {
        if (empty($frameWidth) && empty($frameHeight)) {
            throw new Exception('Invalid image dimensions.');
        }

        $im = $this->_imageHandler;
        $src_width = $im->getImageWidth();
        $src_height = $im->getImageHeight();
        $border_color = '#FFFFFF';

        $scale = false;
        // calculate lacking dimension
        if (!$this->_keepFrame) {
            if ($frameHeight == 0) {
                $frameHeight = round(($src_height * $frameWidth) / $src_width);
                $scale = true;
            } elseif ($frameWidth == 0) {
                $frameWidth = round(($src_width * $frameHeight) / $src_height);
                $scale = true;
            }
        } else {
            if (null === $frameWidth) {
                $frameWidth = $frameHeight;
            } elseif (null === $frameHeight) {
                $frameHeight = $frameWidth;
            }
        }
        $width = $frameWidth;
        $height = $frameHeight;

        if ($this->_keepAspectRatio) {
            if ($this->_constrainOnly) {
                $th_porp = $frameWidth / $frameHeight;
                if ($src_width < $frameWidth || $src_height < $frameHeight) {
                    $width = ($src_width < $frameWidth) ? $src_width : $frameWidth;
                    $height = ($src_height < $frameHeight) ? $src_height : $frameWidth / $th_porp;
                }
            }

            // keep aspect ratio
            if ($src_width / $src_height >= $width / $height) {
                $height = round(($width / $src_width) * $src_height);
            } else {
                $width = round(($height / $src_height) * $src_width);
            }
        }

        $sharpen = true;

        if ($this->_keepFrame /* && ($src_width < $frameWidth || $src_height < $frameHeight)*/) {

            $im->scaleImage($frameWidth, $frameHeight, true);

            $src_width = $im->getImageWidth();
            $src_height = $im->getImageHeight();

            $border_x = max(0, round(($frameWidth - $src_width) / 2));
            $border_y = max(0, round(($frameHeight - $src_height) / 2));
            if ($border_x > 0 || $border_y > 0) {
                $im->borderImage($border_color, $border_x, $border_y);
            }
            $im->cropThumbnailImage($frameWidth, $frameHeight);
        } else {
            if ($scale) {
                $im->scaleImage($width, $height, true);
            } else {
                $im->cropThumbnailImage($width, $height);
            }
        }
        if ($sharpen) {
            $im->unsharpMaskImage(50, 0.5, 1, 0.05);
        }

        $this->_refreshImageDimensions();
        $this->_resized = true;
    }

    /**
     * Rotate image
     *
     * @param $angle
     *
     */
    public function rotate($angle)
    {
        $bg_color = new ImagickPixel($this->_fileType == IMAGETYPE_PNG ? 'none' : '#FFFFFF');
        $this->_imageHandler->rotateImage($bg_color, $angle);
        $this->_refreshImageDimensions();
    }

    private function _refreshImageDimensions()
    {
        $this->_imageSrcWidth = $this->_imageHandler->getImageWidth();
        $this->_imageSrcHeight = $this->_imageHandler->getImageHeight();
    }

    /**
     * Crop image
     *
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     *
     * @link http://php.net/manual/en/imagick.cropimage.php
     *
     * @throws ImagickException
     */
    public function crop($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        if ($left == 0 && $top == 0 && $right == 0 && $bottom == 0) {
            return;
        }

        /**
         * @var $newWidth - The width of the crop
         * @var $newHeight - The height of the crop
         * @var $x - The X coordinate of the cropped region's top left corner
         * @var $y - The Y coordinate of the cropped region's top left corner
         */
        $newWidth = $this->_imageSrcWidth - $left - $right;
        $newHeight = $this->_imageSrcHeight - $top - $bottom;

        $this->_imageHandler->cropImage($newWidth, $newHeight, $left, $top);

        $this->_refreshImageDimensions();
    }

    /**
     * Add watermark on image
     *
     * @param $watermarkImage
     * @param int $positionX
     * @param int $positionY
     * @param int $watermarkImageOpacity
     * @param bool $repeat
     * @return void
     */
    public function watermark($watermarkImage, $positionX = 0, $positionY = 0, $watermarkImageOpacity = 30, $repeat = false)
    {
        $watermark = new Imagick($watermarkImage);
        if (!$watermark) {
            return;
        }

        // Magento is not using the $watermarkImageOpacity parameter in Core GD2 adapter
        // and uses the adapter instance variable/method getWatermarkImageOpacity() instead.
        // So we need to do this also.
        $watermarkImageOpacity = $this->getWatermarkImageOpacity();
        if(isset($watermarkImageOpacity) && $watermarkImageOpacity!="") {
            $watermark->setImageOpacity($watermarkImageOpacity / 100);
        }

        if ($repeat) {
            // tile watermark
            $this->_imageHandler->textureImage($watermark);
        } else {
            $this->_imageHandler->compositeImage($watermark, $watermark->getImageCompose(), $positionX, $positionY, Imagick::COLOR_ALPHA);
        }

        $watermark->destroy();
    }

    public function checkDependencies()
    {
        if (!class_exists('Imagick', false)) {
            throw new Exception("Required PHP class 'Imagick' was not loaded.");
        }
    }

    public function display()
    {
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: " . $this->getMimeType());

        $blob = $this->_imageHandler->getImageBlob();
        header("Content-Length: " . strlen($blob));

        echo $blob;
    }

    public function setOutputType($type)
    {
        if ($type) {
            $this->getMimeType();
            if (!$this->_inputType) {
                $this->_inputType = $this->_fileType;
            }
            $this->_fileType = $type;
        }
    }

    public function __destruct()
    {
        if ($this->_imageHandler) {
            $this->_imageHandler->clear();
            $this->_imageHandler->destroy();
            unset($this->_imageHandler);
        }
    }
}
