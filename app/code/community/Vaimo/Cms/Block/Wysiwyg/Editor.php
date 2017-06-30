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

class Vaimo_Cms_Block_Wysiwyg_Editor extends Vaimo_Cms_Block_Content_Editor_Abstract
{
    protected $_jsClassName = 'cmsWysiwygEditor';
    protected $_controller = 'raptor';

    protected $_constructorParams = array(
        'uriSave' => '',
        'overlayManager' => '',
        'fileManager' => array(
            'uriPublic' => '',
            'uriAction' => '',
            'uriIcon' => ''
        ),
        'imageEditor' => array(
            'uriSave' => ''
        ),
        'attributes' => array(
            'id' => 'block-id',
            'parents' => array(
                'structure-id',
                'vcms-widget-page-id'
            )
        ),
        'selectors' => array(
            'editButton' => '.vcms-wysiwyg-edit',
            'editableBlocks' => '',
            'container' => 'body'
        ),
    );

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $factory = $this->getFactory();

        $fileManager = isset($args['fileManager']) ?
            $args['fileManager'] : $factory->getModel('vaimo_cms/fileManager');

        $selectors = implode(',', $factory->getHelper('vaimo_cms')->getEditableBlocksSelectors());

        $this->addConstructorParams(array(
            'fileManager' => array(
                'uriPublic' => $fileManager->getStorageUrl(),
                'uriAction' => $this->_getAdminUrl('fileManager'),
                'uriIcon' => $this->_getAdminUrl('thumbnail/')
            ),
            'selectors' => array(
                'editableBlocks' => $selectors
            ),
            'imageEditor' => $this->_getAdminUrl('imageEditor'),
            'uriSave' => $this->_getUrl(Vaimo_Cms_Model_Wysiwyg_Editor::UPDATE_ACTION),
            'protectedParams' => $factory->getHelper('vaimo_cms/editor')->getEditorParamNames()
        ));
    }
}