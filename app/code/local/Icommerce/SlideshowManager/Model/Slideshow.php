<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
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
 * @category    Vaimo
 * @package     Icommerce_SlideshowManager
 * @author      Rory O'Connor <rory.oconnor@vaimo.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Icommerce_SlideshowManager_Model_Slideshow extends Mage_Core_Model_Abstract
{
    private $_slideshow = array();

    protected function _construct()
    {
        $this->_init('slideshowmanager/slideshow');
    }

    public function getSlideshows()
    {

        $r = Icommerce_Db::getDbRead();
        $rows = $r->query("SELECT * FROM icommerce_slideshow ORDER BY position ASC");

        $returnArray = array();

        foreach ($rows as $key => $value) {
            $returnArray[$key] = $value;
        }

        return $returnArray;
    }

    public function getSlideshow($slideshowId)
    {
        $slideshowId = (int )$slideshowId;
        if (array_key_exists($slideshowId, $this->_slideshow)) {
            return $this->_slideshow[$slideshowId];
        }
        $this->_slideshow[$slideshowId] = (array )Icommerce_Db::getRow("SELECT * FROM icommerce_slideshow WHERE id = $slideshowId");

        return $this->_slideshow[$slideshowId];
    }

    public function getSlideshowHeight($slideshowId)
    {
        $slideshow = $this->getSlideshow($slideshowId);

        return isset($slideshow['height']) ? $slideshow['height'] : 0;
    }

    public function getSlideshowWidth($slideshowId)
    {
        $slideshow = $this->getSlideshow($slideshowId);

        return isset($slideshow['width']) ? $slideshow['width'] : 0;
    }

    public function getSlideshowThumbnails($slideshowId)
    {
        $slideshow = $this->getSlideshow($slideshowId);

        return isset($slideshow['thumbnails']) ? $slideshow['thumbnails'] : 0;
    }

    /**
     * Used for example to populate slideshow widgets
     * @return array
     */
    public function toOptionArray()
    {
        $slideshows = $this->getSlideshows();

        $options = array();
        foreach($slideshows as $slideshow){
            $options[] = array('value' => $slideshow['id'], 'label' => $slideshow['name']);
        }

        return $options;
    }
}
