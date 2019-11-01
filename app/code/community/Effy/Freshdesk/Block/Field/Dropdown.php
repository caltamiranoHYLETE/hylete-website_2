<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Block_Field_Abstract
 */
class Effy_Freshdesk_Block_Field_Dropdown extends Effy_Freshdesk_Block_Field_Abstract
{
    const CLASS_NAME_SELECT = 'select';    
    const URL_COMPANY_OPTIONS   = 'api/v2/companies';

    public function _construct()
    {
        $this->setTemplate('freshdesk/field/dropdown.phtml');

        parent::_construct();
    }

    public function getOptions()
    {        
        if ($this->_getData('choices') === null) {
            if(strcmp($this->getFieldLabel(), 'Company') == 0) {                    
                try {
                    $companyModel = Mage::getModel('effy_freshdesk/freshdesk_company');                    
                    $response = $companyModel->getCompanies();
                    $choices[] = array(
                        'label' => $this->_helper()->__('...'),
                        'value' => ''
                    );
                    foreach ($response as $key => $value) {
                        $choices[] = array(
                            'label' => $value['name'],
                            'value' => $value['id']
                        );                        
                    }
                    $this->setData('choices', $choices);
                }                
                catch(Exception $e) {
                    Mage::log('Error');
                    Mage::log($e->message());
                }
            }
            else {
                $choices[] = array(
                    'label' => $this->_helper()->__('...'),
                    'value' => ''
                );
                foreach ($this->getField()->getChoices() as $label => $choice) {
                    if (empty($choice)) {
                        continue;
                    }
                    if (!is_array($choice)) {
                        if(!is_numeric($label)) {
                            $choices[] = array(
                                'label' => strval($label), 
                                'value' => strval($choice)
                            );
                        }
                        else {
                            $choices[] = array(
                                'label' => strval($choice),
                                'value' => strval($choice)
                            );
                        }
                    } else {
                        $choices[] = array(
                            'label' => strval($choice[1]),
                            'value' => strval($choice[0])
                        );
                    }
                }                
                $this->setData('choices', $choices);
            }
        }        
        return $this->_getData('choices');
    }

    protected function _beforeToHtml()
    {
        $this->addFieldClass(self::CLASS_NAME_SELECT);

        return parent::_beforeToHtml();
    }
}