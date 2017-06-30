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
 * @package     Vaimo_CustomCss
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Vaimo_CustomCss_Block_Adminhtml_Customcss_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'customcss';
        $this->_controller = 'adminhtml_customcss';
        $this->_mode = 'edit';
        $this->_updateButton('save', 'label', Mage::helper('customcss')->__('Save CSS File'));
        $this->_updateButton('save', 'onclick', 'save()');
        $this->_updateButton('delete', 'label', Mage::helper('customcss')->__('Delete'));
        $this->_addButton('saveandcontinue', array(
                'label' => Mage::helper('customcss')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
        ), -100);

        $theme = Mage::getStoreConfig('customcss/settings/theme');

        $this->_formScripts[] = "
            var codeEl = $('customcss_code');
            var editorEl = new Element('pre', { 'id': 'css-editor' }).update(codeEl.getValue());
            $('customcss_code').insert({ after: editorEl });

            var editor = ace.edit('css-editor');
            editor.setTheme('ace/theme/{$theme}');
            editor.getSession().setMode('ace/mode/css');

            function save() {
                codeEl.setValue(editor.getValue());
                editForm.submit();
            }

            function saveAndContinueEdit(){
                codeEl.setValue(editor.getValue());
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('customcss_data') && Mage::registry('customcss_data')->getId()) {
            return Mage::helper('customcss')->__('Edit CSS File');
        } else {
            return Mage::helper('customcss')->__('New CSS File');
        }
    }
}