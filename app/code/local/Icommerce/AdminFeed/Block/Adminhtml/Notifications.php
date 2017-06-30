<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
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
 * @package     Icommerce_AdminFeed
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */
class Icommerce_AdminFeed_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{       
    public function getNotifications()
	{
		return Mage::getModel('adminfeed/notifications')->getNotifications();
	}
	
	public function isRead($id)
	{
		return (bool)Mage::getModel('adminfeed/notifications')->isRead($id);
	}
	
	public function getSeverity($level)
	{
		if($level == 1)
			return "<span class='grid-severity-critical'><span>".$this->__('critical')."</span></span>";
		elseif($level == 2)
			return "<span class='grid-severity-major'><span>".$this->__('major')."</span></span>";
		elseif($level == 3)
			return "<span class='grid-severity-minor'><span>".$this->__('minor')."</span></span>";
		elseif($level == 4)
			return "<span class='grid-severity-notice'><span>".$this->__('notice')."</span></span>";
	}	
}