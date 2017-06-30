<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 *
 * @method string getIncrementId()
 * @method Vaimo_IntegrationBase_Model_Shipment setIncrementId(string $value)
 */

class Vaimo_IntegrationBase_Model_Shipment extends Vaimo_IntegrationBase_Model_Abstract
{
    protected $_items = null;
    protected $_tracks = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/shipment');
    }

    /**
     * Save shipment and it's items collection
     *
     * @return $this|Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        if (null !== $this->_items) {
            $this->getItemsCollection()->save();
        }
        if (null !== $this->_tracks) {
            $this->getTracksCollection()->save();
        }
        return $this;
    }

    /**
     * Get shipment import data items collection
     *
     * @return mixed|null|object
     */
    public function getItemsCollection()
    {
        if ($this->hasItemsCollection()) {
            return $this->getData('items_collection');
        }
        if (is_null($this->_items)) {
            $this->_items = Mage::getModel('integrationbase/shipment_item')->getCollection();
            $this->_items->setShipment($this);
        }
        return $this->_items;
    }

    /**
     * Get shipment import data items as an array of entities
     *
     * @return array
     */
    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            $items[] =  $item;
        }
        return $items;
    }

    /**
     * Add item import data to shipment import data
     *
     * @param Vaimo_IntegrationBase_Model_Shipment_Item $item
     * @return $this
     */
    public function addItem(Vaimo_IntegrationBase_Model_Shipment_Item $item)
    {
        $item->setShipment($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    public function getTracksCollection()
    {
        if ($this->hasTracksCollection()) {
            return $this->getData('tracks_collection');
        }
        if (is_null($this->_tracks)) {
            $this->_tracks = Mage::getModel('integrationbase/shipment_track')->getCollection();
            $this->_tracks->setShipment($this);
        }
        return $this->_tracks;
    }

    public function getAllTracks()
    {
        $tracks = array();
        foreach ($this->getTracksCollection() as $track) {
            $tracks[] =  $track;
        }
        return $tracks;
    }

    public function addTrack(Vaimo_IntegrationBase_Model_Shipment_Track $track)
    {
        $track->setShipment($this);
        if (!$track->getId()) {
            $this->getTracksCollection()->addItem($track);
        }
        return $this;
    }
}