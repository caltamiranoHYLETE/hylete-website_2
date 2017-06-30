<?php
/**
 * Icommerce
 * 
 */
class Icommerce_PaymentShared_Model_Quote_Address extends Mage_Sales_Model_Quote_Address
{
    
    /**
     * Validate address attribute values
     *
     * @return bool
     */
    public function validate()
    {
    	$errors = parent::validate();
    	if( $errors==true ) return true;
    
        $helper = Mage::helper('customer');
    	$err_str = $helper->__('Please enter state/province.');  // Don't validate region
    	foreach( $errors as $ix => $error )
    	{
    		if( $error==$err_str )
    			unset( $errors[$ix] );
    	}
    	
        if( empty($errors) )
        	return true;
		
		/* If loaded in 1.3.2.4, MatrixRates will not fully work */
		if(Icommerce_Default::getVersion() >= 1400)
		{
        	if( $this->getShouldIgnoreValidation() )
        		return true; 
    	}
    	return $errors;
    }
}
