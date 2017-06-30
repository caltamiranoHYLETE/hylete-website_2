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
 * @package     Icommerce_PageManager
 * @author      Rory O'Connor <rory.oconnor@vaimo.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Icommerce_PageManager_Model_Page extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('pagemanager/page');
    }

	function getPages(){

		$r = Icommerce_Db::getDbRead();
        $rows = $r->query( "SELECT * FROM icommerce_pagemanager ORDER BY position ASC" );

		$returnArray = array();

		foreach($rows as $key => $value){
			$returnArray[$key] = $value;
		}

		return $returnArray;
	}

	function getPage($pageId){
		$row = Icommerce_Db::getRow("SELECT * FROM icommerce_pagemanager WHERE id = $pageId");

		return $row;
	}

    function getPageIdFromName($name){
        return Icommerce_Db::getValue("SELECT id FROM icommerce_pagemanager WHERE name like ?", array($name));
    }
}
