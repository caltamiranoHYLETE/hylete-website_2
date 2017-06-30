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

class Vaimo_Cms_Block_Page_Editor extends Vaimo_Cms_Block_Content_Editor_Abstract
{
    protected $_jsClassName = 'cmsPageEditor';

    protected $_constructorParams = array(
        'uri' => array(
            'save' => '',
            'cancel' => ''
        ),
        'selectors' => array(
            'toolbarButtonsGroup' => '#js-vcms-staging-button-group',
            'toolbarButtons' => array(
                'save' => '#js-vcms-publish-draft',
                'cancel' => '#js-vcms-discard-draft'
            ),
        ),
        'attributes' => array(
            'content' => array(
                'name' => 'data-child-of',
                'delimiter' => Vaimo_Cms_Model_Markup_Updater::MULTI_VALUE_DELIMITER
            )
        ),
        'revision' => '',
        'layoutHandle' => '',
        'initial' => array()
    );

    protected function _construct()
    {
        $revisionId = Vaimo_Cms_Helper_Page::DRAFT;
        $editorModel = $this->getFactory()->getSingleton('vaimo_cms/page_editor');

        $this->addConstructorParams(array(
            'uri' => array(
                'save' => $this->_getUrl(Vaimo_Cms_Model_Page_Editor::PUBLISH_ACTION),
                'cancel' => $this->_getUrl(Vaimo_Cms_Model_Page_Editor::DISCARD_ACTION)
            ),
            'layoutHandle' => $editorModel->getCurrentLayoutHandle(),
            'initial' => $editorModel->always(array('revision' => $revisionId)),
            'revision' => $revisionId
        ));
    }
}