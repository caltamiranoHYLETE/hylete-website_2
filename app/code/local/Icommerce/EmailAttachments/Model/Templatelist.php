<?php
class Icommerce_EmailAttachments_Model_Templatelist
{
	public function toOptionArray($addEmpty = true)	{

	    // http://blog.decryptweb.com/email-templates-in-magento/
	    $attribute = Mage::getResourceSingleton('core/email_template_collection');
	    $options = array();

        foreach ( $attribute as $option){
            $label[]=$option->getTemplateCode(); // Be able to sort the list later
            $value[]=$option->getTemplateId();
            $options[] = array(
                'label' => $option->getTemplateCode(),
                'value' => $option->getTemplateId()
            );
        }

        array_multisort($label, SORT_ASC, $value, SORT_ASC, $options); // Sort the list

        $addEmpty = true; // Always add an empty row (2012-02-17 Peter L)
        if ($addEmpty) {
            $emptyline['label'] = Mage::helper('adminhtml')->__('-- ignore, uses setting above --');
            $emptyline['value'] = '';
            array_unshift($options,$emptyline);
        }
        return $options;
    }

	function custom_sort($a,$b) {
        return $a['label']>$b['label'];
	}
}
