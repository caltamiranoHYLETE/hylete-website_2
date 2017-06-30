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
 * @package     Vaimo_Infinitescroll
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Vaimo_InfiniteScroll_Block_Page_Pager extends Mage_Page_Block_Html_Pager
{
    protected $_settingTemplate = 'vaimo/infinitescroll/settings.phtml';
    protected $_block = null;

    //FIXME find better solution
    protected function _getConfigurationBlockHtml()
    {
        if (null === $this->_block) {
            $_block = $this->getLayout()->createBlock('core/template');
            $_block->setTemplate('vaimo/infinitescroll/settings.phtml');
        }

        $_block->setLastPageNum($this->getLastPageNum());
        $_block->setCurrentPage($this->getCurrentPage());

        return $_block->toHtml();
    }


    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if ($html) {
            $html .= $this->_getConfigurationBlockHtml();
        }
        return $html;
    }
}
