<?php

class Icommerce_Scheduler_Block_Adminhtml_Operation_Edit_Tab_Details extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $schedulerOperations = Mage::helper('scheduler')->getDefinedSchedulerOperations();
        $codeOptions = array('' => '');
        foreach ($schedulerOperations as $key => $value) {
            $codeOptions[$key] = $value['label'];
        }

        $allOperations = array('' => '');
        $operations = Mage::getModel('scheduler/operation')->getCollection()->getItems();
        foreach ($operations as $operation) {
            $allOperations[$operation->getId()] = isset($codeOptions[$operation->getCode()]) ? $codeOptions[$operation->getCode()] : $operation->getCode();
        }

        $actionforward = $this->getRequest()->getBeforeForwardInfo('action_name');
        $actionmethod = $this->getRequest()->getActionName();

        $general = $form->addFieldset('general_form', array('legend' => $this->__('Details')));

        $general->addField('code', 'select', array(
            'name'      => 'code',
            'label'     => Mage::helper('scheduler')->__('Task'),
            'required'  => true,
            'options'   => $codeOptions,
        ));

//        $general->addField('name', 'text', array(
//            'name'      => 'name',
//            'label'     => Mage::helper('scheduler')->__('Name'),
//            'required'  => false,
//        ));

        /** @var $dependenceBlock Mage_Adminhtml_Block_Widget_Form_Element_Dependence */
        $dependenceBlock = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $dependenceBlock->addFieldMap('code', 'code');
        $parameters = null;

        // add fields
        foreach ($schedulerOperations as $operationCode => $schedulerOperation) {
            if (isset($schedulerOperation['fields'])) {
                foreach ($schedulerOperation['fields'] as $fieldName => $fieldData) {
                    $elementId = $operationCode . '[' . $fieldName . ']';
                    $fieldType = (isset($fieldData['frontend_type']) ? $fieldData['frontend_type'] : 'text');

                    $config = array();
                    $config['label'] = (isset($fieldData['label']) ? $fieldData['label'] : '(label missing)');
                    $config['required'] = (isset($fieldData['required']) ? $fieldData['required'] : false);
                    $config['name'] = $elementId;

                    if ($fieldType == 'date') {
                        $config['image'] = $this->getSkinUrl('images/grid-cal.gif');
                        if (isset($fieldData['format'])) {
                            $config['format'] = $fieldData['format'];
                        } else {
                            $config['format'] = Mage::app()->getLocale()->getDateFormatWithLongYear();
                        }

                        // This will be used to assess if the format conversion (to JS dateTime format) should also include time
                        if ($config['time'] = preg_match('/[Hms]/', $config['format'])) {
                            // Making the field a bit longer to allow time to display correctly
                            $config['style'] = 'width:130px;';
                        }
                    }

                    if (isset($fieldData['comment'])) {
                        $config['after_element_html'] = '<p class="note"><span>' . $fieldData['comment'] . '</span></p>';
                    }

                    if ($parameters == null) {
                        $parameters = $form->addFieldset('parameters', array('legend' => $this->__('Parameters')));
                    }

                    $field = $parameters->addField($elementId, $fieldType, $config);

                    if (isset($fieldData['source_model'])) {
                        $factoryName = (string)$fieldData['source_model'];
                        $method = false;

                        if (preg_match('/^([^:]+?)::([^:]+?)$/', $factoryName, $matches)) {
                            array_shift($matches);
                            list($factoryName, $method) = array_values($matches);
                        }

                        $sourceModel = Mage::getSingleton($factoryName);

                        if ($method) {
                            if ($fieldType == 'multiselect') {
                                $optionArray = $sourceModel->$method();
                            } else {
                                $optionArray = array();
                                foreach ($sourceModel->$method() as $value => $label) {
                                    $optionArray[] = array('label' => $label, 'value' => $value);
                                }
                            }
                        } else {
                            $optionArray = $sourceModel->toOptionArray($fieldType == 'multiselect');
                        }
                        $field->setValues($optionArray);
                    }

                    if (isset($fieldData['frontend_model'])) {
                        $fieldRenderer = Mage::getBlockSingleton((string)$fieldData['frontend_model']);
                        $fieldRenderer->setForm($this);
                        $field->setRenderer($fieldRenderer);
                    }

                    $dependenceBlock->addFieldMap($elementId, $elementId);
                    $dependenceBlock->addFieldDependence($elementId, 'code', $operationCode);
                }
            }
        }

        if ($parameters != null) {
            $this->setChild('form_after', $dependenceBlock);
        }

        $general->addField('comment', 'textarea', array(
            'name'      => 'comment',
            'label'     => Mage::helper('scheduler')->__('Comment'),
            'required'  => false,
        ));

        $general->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => Mage::helper('scheduler')->__('Status'),
            'required'  => true,
            'values'    => Mage::helper('scheduler')->getOperationStatusesOptionArray(true),
        ));

        $general->addField('save_history', 'multiselect', array(
            'name'      => 'save_history[]',
            'label'     => Mage::helper('scheduler')->__('Save History for Status'),
            'values'    => Mage::helper('scheduler')->getHistoryStatusesMultiOptionArray(),
        ));

        $general->addField('url_override', 'text', array(
            'name'      => 'url_override',
            'label'     => Mage::helper('scheduler')->__('Base URL if different from default'),
            'required'  => false,
        ));

        $authentication = $form->addFieldset('authentication_form', array('legend' => $this->__('HTTP Authentication')));

        $authentication->addField('authentication_type', 'select', array(
            'name'      => 'authentication_type',
            'label'     => Mage::helper('scheduler')->__('Authentication Type'),
            'required'  => false,
            'values'    => Mage::helper('scheduler')->getAuthenticationTypes(),
        ));

        $authentication->addField('username', 'text', array(
            'name'      => 'username',
            'label'     => Mage::helper('scheduler')->__('Username'),
            'required'  => false,
        ));

        $authentication->addField('password', 'password', array(
            'name'      => 'password',
            'label'     => Mage::helper('scheduler')->__('Password'),
            'required'  => false,
            'after_element_html' => '<p class="note"><span>If trigger function requires 401 authentication, then specify type, username and password here</span></p>',
        ));

        $recurrence = $form->addFieldset('recurrence_form', array('legend' => $this->__('Recurrence Information')));

        $recurrence->addField('recurrence_info[frequency]', 'select', array(
            'name'      => 'recurrence_info[frequency]',
            'label'     => Mage::helper('scheduler')->__('Frequency'),
            'values'    => Mage::helper('scheduler')->getOperationRecurrenceOptionArray('frequency'),
        ));

        $recurrence->addField('recurrence_info[n]', 'select', array(
            'name'      => 'recurrence_info[n]',
            'label'     => Mage::helper('scheduler')->__('n'),
            'values'    => Mage::helper('scheduler')->getOperationRecurrenceOptionArray('n'),
        ));

        $recurrence->addField('recurrence_info[hour]', 'select', array(
            'name'      => 'recurrence_info[hour]',
            'label'     => Mage::helper('scheduler')->__('Hour'),
            'values'    => Mage::helper('scheduler')->getOperationRecurrenceOptionArray('hour'),
        ));

        $recurrence->addField('recurrence_info[minute]', 'select', array(
            'name'      => 'recurrence_info[minute]',
            'label'     => Mage::helper('scheduler')->__('Minute'),
            'values'    => Mage::helper('scheduler')->getOperationRecurrenceOptionArray('minute'),
        ));

        $recurrence->addField('recurrence_info[weekday]', 'select', array(
            'name'      => 'recurrence_info[weekday]',
            'label'     => Mage::helper('scheduler')->__('Weekday'),
            'values'    => Mage::helper('scheduler')->getOperationRecurrenceOptionArray('weekday'),
        ));

        $recurrence->addField('recurrence_info[day]', 'select', array(
            'name'      => 'recurrence_info[day]',
            'label'     => Mage::helper('scheduler')->__('Day'),
            'values'    => Mage::helper('scheduler')->getOperationRecurrenceOptionArray('day'),
        ));

        $recurrence->addField('recurrence_info[month]', 'select', array(
            'name'      => 'recurrence_info[month]',
            'label'     => Mage::helper('scheduler')->__('Month'),
            'values'    => Mage::helper('scheduler')->getOperationRecurrenceOptionArray('month'),
        ));

        $rerun = $form->addFieldset('rerun_form', array('legend' => $this->__('Rerun task on failed status')));

        $rerun->addField('rerun', 'select', array(
            'name'      => 'rerun',
            'label'     => Mage::helper('scheduler')->__('Enable'),
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $rerun->addField('rerun_count', 'select', array(
            'name'      => 'rerun_count',
            'label'     => Mage::helper('scheduler')->__('Rerun tries count'),
            'values'    => Mage::helper('scheduler')->getRerunCountOptionArray(),
        ));

        $master = $form->addFieldset('master_form', array('legend' => $this->__('Master Slave Dependency')));

        $master->addField('master_id', 'select', array(
            'name'      => 'master_id',
            'label'     => Mage::helper('scheduler')->__('Master ID (if this is a slave)'),
            'values'    => $allOperations,
        ));

        $master->addField('master_order', 'text', array(
            'name'      => 'master_order',
            'label'     => Mage::helper('scheduler')->__('Order'),
            'required'  => false,
        ));

        $email = $form->addFieldset('email_form', array('legend' => $this->__('Email')));

        $email->addField('email_enabled', 'select', array(
            'name'      => 'email_enabled',
            'label'     => Mage::helper('scheduler')->__('Send Email'),
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $email->addField('email_status', 'multiselect', array(
            'name'      => 'email_status[]',
            'label'     => Mage::helper('scheduler')->__('Send Email for Status'),
            'values'    => Mage::helper('scheduler')->getHistoryStatusesMultiOptionArray(),
        ));

        $email->addField('email_template', 'select', array(
            'name'      => 'email_template',
            'label'     => Mage::helper('scheduler')->__('Email Template'),
            'values'    => Mage::getModel('adminhtml/system_config_source_email_template')->toOptionArray(),
        ));

        $email->addField('email_sender', 'select', array(
            'name'      => 'email_sender',
            'label'     => Mage::helper('scheduler')->__('Email Sender'),
            'values'    => Mage::getModel('adminhtml/system_config_source_email_identity')->toOptionArray(),
        ));

        $email->addField('email_receiver', 'text', array(
            'name'      => 'email_receiver',
            'label'     => Mage::helper('scheduler')->__('Email Recipients'),
            'required'  => false,
            'after_element_html' => '<p class="note"><span>Comma-separated.</span></p>',
        ));

        /** @var $operation Icommerce_Scheduler_Model_Operation */
        if ($operation = Mage::registry('operation_data')) {
            $data = $operation->getData();
            if ($operation->getRecurrenceInfo()) {
                foreach ($operation->getRecurrenceInfo() as $key => $value) {
                    $data['recurrence_info[' . $key . ']'] = $value;
                }
            }
            if ($operation->getCode() && $operation->getParameters()) {
                foreach ($operation->getParameters() as $key => $value) {
                    $data[$operation->getCode() . '[' . $key . ']'] = $value;
                }
            }

            if (!isset($data['save_history'])) {
                $statuses = Mage::helper('scheduler')->getHistoryStatusesOptionArray();
                $data['save_history'] = implode(',', array_keys($statuses));
            }

            $form->setValues($data);
        }

        return parent::_prepareForm();
    }
}