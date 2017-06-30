<?php
class Vaimo_Gotoadmin_Model_Observer {
    private $cmsBlocks = array();
    
    public function getCmsBlocks() { 
        asort($this->cmsBlocks);
        return $this->cmsBlocks;
    }
    
	public function filterCmsBlocks(Varien_Event_Observer $observer) {        
        $block = $observer->getBlock();
        
        if ($block->getBlockId() && !ctype_digit($block->getBlockId())) { //Not showing blocks numbered identifiers (to get rid of vaimo_cms-generated blocks)
            $this->cmsBlocks[] = $block->getBlockId();
        }
    }
}

