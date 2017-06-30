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

class Icommerce_SlideshowManager_Model_Item extends Mage_Core_Model_Abstract
{
    /** @var $_db_read Varien_Db_Adapter_Pdo_Mysql */
    protected $_db_read;

    protected function _construct()
    {
        $this->_init('slideshowmanager/item');
        $this->_db_read = Icommerce_Db::getDbRead();
    }

    public function getItemType()
    {
        $hlp = Mage::helper('slideshowmanager');
        return array(
            array('value' => 'image', 'label' => $hlp->__('Image')),
            array('value' => 'html',  'label' => $hlp->__('HTML')),
        );
    }

    public function getItems($slideshowId)
    {

        $sql = 'SELECT * FROM icommerce_slideshow_item WHERE slideshow_id = ? ORDER BY position ASC';

        $rows = $this->_db_read->fetchAll($sql, array($slideshowId));
        return (array )$rows;
    }

    public function getItem($itemId)
    {
        $itemId = (int )$itemId;
        $row = $this->_db_read->fetchRow('SELECT * FROM icommerce_slideshow_item WHERE id = ?', array($itemId));

        return $row;
    }

    /**
     * This function is used to get the published items for frontend.
     */

    public function getSlideshowItems($slideshowId)
    {
        $slideshowId = (int )$slideshowId;
        /** @var $select Varien_Db_Select */
        $select = $this->_db_read->select();
        $select
            ->from(array('si' => 'icommerce_slideshow_item'))
            ->joinInner(array('s' => 'icommerce_slideshow'), 'si.slideshow_id = s.id', array())
            ->where('s.status = \'1\' AND si.status = \'1\' AND si.slideshow_id = ?', $slideshowId);
        $select->order('si.position');

        $rows = $this->_db_read->fetchAll($select);
        return (array )$rows;
    }
}
