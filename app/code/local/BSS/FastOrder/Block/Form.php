<?php
/**
 * @category	BSS
 * @package	BSS_Fastorder
 */

class BSS_FastOrder_Block_Form extends Mage_Core_Block_Template
{
    public $lines = 3; //Number of lines showed on the form
    public $minAutocomplete = 3; //number of character before showing autocomplete results
    public $maxResults = 10; //Max results to show
    public $allowedCharacters = "azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN1234567890";
    
    public function __construct(){
        $this->lines = (int) Mage::getStoreConfig('bss_fastorder/general_settings/lines');
        $this->minAutocomplete = (int) Mage::getStoreConfig('bss_fastorder/general_settings/min_autocomplete');
        $this->maxResults = (int) Mage::getStoreConfig('bss_fastorder/general_settings/max_results');
        $this->allowedCharacters = Mage::getStoreConfig('bss_fastorder/general_settings/allowed_characters');
        
        $this->setTemplate('bss/fastorder/form.phtml');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getLines()
    {
        return $this->lines;
    }
    
    public function getMinAutocomplete()
    {
        return $this->minAutocomplete;
    }
    
    public function getMaxResults()
    {
        return $this->maxResults;
    }
    
    public function setLines($value)
    {
        $this->lines = $value;
        return $this;
    }
    
    public function setMinAutocomplete($value)
    {
        $this->minAutocomplete = $value;
        return $this;
    }
    
    public function setMaxResults($value)
    {
        $this->maxResults = $value;
        return $this;
    }
    
    public function getAllowedCharacters()
    {
        return $this->allowedCharacters;
    }
    
    public function setAllowedCharacters($value)
    {
        $this->allowedCharacters = $value;
        return $this;
    }
}
