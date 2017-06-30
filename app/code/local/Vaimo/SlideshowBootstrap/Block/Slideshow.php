<?php

/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @package     Vaimo_SlideshowBootstrap
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */
class Vaimo_SlideshowBootstrap_Block_Slideshow
    extends Icommerce_SlideshowManager_Block_Slideshow
    implements Mage_Widget_Block_Interface {

    const TYPE_CATEGORY = 'category';
    const TYPE_PRODUCT = 'product';

    /**
     * @var
     */
    protected $_blockRenderer;

    /**
     * @var array
     */
    protected $_blockHtml = array();

    /**
     * @var array
     */
    protected $_products = array();

    /**
     * @var array
     */
    protected $_categories = array();

    /**
     * @param $blockId
     * 
     * @return mixed
     */
    protected function _getBlockHtml($blockId)
    {
        if (! isset($this->_blockHtml[$blockId]))
        {
            if (! isset($this->_blockRenderer))
            {
                $this->_blockRenderer = $this->getLayout()->createBlock('cms/block');
            }

            $this->_blockHtml[$blockId] = $this->_blockRenderer
                ->setBlockId($blockId)
                ->toHtml();
        }

        return $this->_blockHtml[$blockId];
    }

    /**
     * @param $categorId
     * @return mixed
     */
    protected function _getCategory($categorId)
    {
        if (! isset($this->_categories[$categorId]))
        {
            $this->_categories[$categorId] = Mage::getModel('catalog/category')->load($categorId);
        }

        return $this->_categories[$categorId];
    }

    /**
     * @param $productId
     * @return mixed
     */
    protected function _getProduct($productId)
    {
        if (! isset($this->_products[$productId]))
        {
            $this->_products[$productId] = Mage::getModel('catalog/product')->load($productId);
        }

        return $this->_products[$productId];
    }

    /**
     * @param $item
     * @return array
     */
    public function getHotSpotsForItem($item)
    {
        if (empty($item['hotspots']))
        {
            return array();
        }

        $hotSpots = explode(';', $item['hotspots']);

        $spots = array();

        foreach ($hotSpots as $hotSpot)
        {
            $spot = Zend_Json::decode($hotSpot);

            if (! isset($spot['id']) || ! isset($spot['type']))
            {
                continue;
            }

            $spotEntity = new Varien_Object(array(
                'id'   => $spot['id'],
                'type' => $spot['type'],
                'y'    => $spot['yoffset'] . '%',
                'x'    => $spot['xoffset'] . '%'
            ));

            switch ($spot['type'])
            {
                case self::TYPE_PRODUCT:
                    if ($product = $this->_getProduct($spot['value']))
                    {
                        $spotEntity->setEntity($product);
                    } else
                    {
                        continue;
                    }
                    break;
                case self::TYPE_CATEGORY:
                    if ($category = $this->_getCategory($spot['value']))
                    {
                        $spotEntity->setEntity($category);
                    } else
                    {
                        continue;
                    }
                    break;
                default:
                    $spotEntity->setContent($this->_getBlockHtml($spot['value']));
                    break;
            }

            $spots[$spot['id']] = $spotEntity;
        }

        return $spots;
    }
}