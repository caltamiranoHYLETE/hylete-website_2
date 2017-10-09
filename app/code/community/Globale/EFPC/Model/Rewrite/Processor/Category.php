<?php

class Globale_EFPC_Model_Rewrite_Processor_Category extends Enterprise_PageCache_Model_Processor_Category
{
    use Globale_EFPC_Trait_AddCustomerInfoToPageId;

	/**
	 * Add GE CCC to the regular cache key in InApp flow
	 * @param Enterprise_PageCache_Model_Processor $processor
	 * @return string
	 */
    public function getPageIdInApp(Enterprise_PageCache_Model_Processor $processor)
	{
		$PageId = parent::getPageIdInApp($processor);
		return $this->_appendCustomerInfoToPageId($PageId);
	}


}