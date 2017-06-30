<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Block_Widget_Editor extends Vaimo_Cms_Block_Content_Editor_Abstract
{
    protected $_jsClassName = 'cmsWidgetEditor';
    protected $_controller = 'widget';

    protected $_constructorParams = array(
        'updateUri'         => '',
        'overlayManager'    => '',
        'editorViewUri'     => '',
        'loaderIndicator'   => '',
        'selectors'         => array(
            'editButton'    => '.vcms-widget-configure',
            'container'     => 'body'
        ),
        'attributes'        => array(
            'parents'       => array(
                'data-structure-id'
            )
        ),
        'name'              => 'vcms-widget-editor-dialog',
        'storeId'           => '',
        'history'           => ''
    );

    protected function _construct()
    {
        $this->addConstructorParams(array(
            'updateUri' => $this->_getUrl(Vaimo_Cms_Model_Widget_Editor::UPDATE_ACTION),
            'editorViewUri' => $this->_getAdminUrl('edit'),
            'storeId' => $this->getApp()->getStore()->getId()
        ));
    }
}