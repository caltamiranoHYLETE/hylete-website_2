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
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Queue extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/queue');
    }

    /**
     * Register queue creation date
     *
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
    }

    /**
     * Set exported_at to current date/time
     *
     * @return Vaimo_IntegrationBase_Model_Queue
     */
    public function setAsExported()
    {
        $this->setExportedAt(Mage::getSingleton('core/date')->gmtDate());
        return $this;
    }

    /**
     * Queue an entity (usually for exporting)
     *
     * @param $entity
     * @param $type
     */
    public function add(Varien_Object $entity, $type = false, $allowRepeatedAdd = false)
    {
        if (!$type) {
            if (!($entity instanceof Mage_Core_Model_Abstract)) {
                Mage::throwException('Varien object requires type to be specified');
            }

            if ($name = $entity->getResourceName()) {
                $type = substr($name, strpos($name, '/') + 1);
            }
        }

        if (!Mage::helper('integrationbase')->isAlreadyQueued($type, $entity->getId()) || $allowRepeatedAdd) {
            $this->setEntityTypeCode($type)
                ->setEntityId($entity->getId())
                ->save();
        }

        return $this;
    }

    /**
     * Get as instance of an entity of certain type from current queue item
     *
     * @param $type
     * @return Mage_Core_Model_Abstract
     */
    public function getEntityFromQueueItem($type)
    {
        return Mage::getModel($type)->load($this->getEntityId());
    }
}