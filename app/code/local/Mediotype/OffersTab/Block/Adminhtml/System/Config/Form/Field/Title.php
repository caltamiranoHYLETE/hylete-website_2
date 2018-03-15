<?php

/**
 * Tab title group-based system configuration field.
 * @category  Class
 * @package   Mediotype_OffersTab
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/**
 * Class declaration
 *
 * @category Class_Type_Block
 * @package  Mediotype_OffersTab
 * @author   Rick Buczynski <rick@mediotype.com>
 */

class Mediotype_OffersTab_Block_Adminhtml_System_Config_Form_Field_Title extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Constructor.
     * @return void
     */
    public function __construct()
    {   
        $this->_addAfter        = false;
        $this->_addButtonLabel  = $this->__('Add Title');

        parent::__construct();
    }

    /**
     * Render handler.
     * - Rewrites value setter to support all field types.
     * @param string $output The rendered HTML.
     * @return string
     */
    protected function _toHtml()
    {
        $rows   = $this->getArrayRows();
        $output = parent::_toHtml();

        preg_match('/arrayRow[^=]*/', $output, $result);

        $objectName = trim( ( array_shift( $result ) ) );

        $output .= '
            <script type="text/javascript">
                ' . $objectName . '.setValue = function(container, field, value) {
                    $(container).select(\'[name*="\' + field + \'"]\')[0].setValue(value);
                }
        ';

        foreach ($rows as $id => $row) {
            foreach ($row->getData() as $key => $value) {
                if ($key == '_id') {
                    continue;
                }

                $output .= sprintf(
                    "%s.setValue('%s', '%s', '%s');\n",
                    $objectName,
                    $id,
                    $key,
                    addslashes($value)
                );
            }
        }

        $output .= '
            </script>
        ';

        return $output;
    }

    /**
     * Prepare layout options.
     * @return Mediotype_OffersTab_Block_Adminhtml_System_Config_Form_Field_Title
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addColumn(
            'group_id',
            array(
                'label'     => $this->__('Customer Group'),
                'renderer'  => $this->_getRenderer('mediotype_offerstab/adminhtml_system_config_form_field_renderer_title_group'),
            )
        );

        $this->addColumn(
            'title',
            array(
                'label' => $this->__('Title'),
            )
        );

        return $this;
    }

    /**
     * Generate a column renderer instance.
     * @param string $alias The block alias.
     * @return Mage_Core_Block_Abstract
     */
    protected function _getRenderer($alias)
    {
        return $this->getLayout()->createBlock($alias);
    }
}
