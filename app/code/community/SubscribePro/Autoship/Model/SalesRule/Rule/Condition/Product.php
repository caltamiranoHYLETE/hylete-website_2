<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

/**
 * Product rule condition data model
 *
 */
class SubscribePro_Autoship_Model_SalesRule_Rule_Condition_Product extends Mage_SalesRule_Model_Rule_Condition_Product
{

    // Subscription status constants
    const SUBSCRIPTION_STATUS_ANY       = 1;
    const SUBSCRIPTION_STATUS_NEW       = 2;
    const SUBSCRIPTION_STATUS_REORDER   = 3;

    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);

        // Add Subscribe Pro specific attributes
        $attributes['quote_item_part_of_subscription'] = Mage::helper('salesrule')->__('Subscription - Status');
        $attributes['quote_item_subscription_interval'] = Mage::helper('salesrule')->__('Subscription - Current Interval');
        $attributes['quote_item_subscription_reorder_ordinal'] = Mage::helper('salesrule')->__('Subscription - Re-order Ordinal');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        switch ($this->getAttribute()) {
            case 'quote_item_part_of_subscription':
                // Check quote item attributes
                $itemFulfilsSubscription = $object->getData('item_fulfils_subscription');
                $itemCreatesNewSubscription = $object->getData('create_new_subscription_at_checkout');
                // Get value set on rule condition
                $conditionValue = $this->getValueParsed();
                // Get operator set on rule condition
                $op = $this->getOperatorForValidate();
                // Handle different status types
                switch ($conditionValue) {
                    case self::SUBSCRIPTION_STATUS_ANY:
                        $matchResult = (bool) ($itemFulfilsSubscription || $itemCreatesNewSubscription);
                        break;
                    case self::SUBSCRIPTION_STATUS_NEW:
                        $matchResult = (bool) $itemCreatesNewSubscription;
                        break;
                    case self::SUBSCRIPTION_STATUS_REORDER:
                        $matchResult = (bool) ($itemFulfilsSubscription && !$itemCreatesNewSubscription);
                        break;
                    default:
                        $matchResult = false;
                        break;
                }
                // Only == or != operators supported
                // In case of !=, do invert $matchResult
                if($op != '==') {
                    $matchResult = !$matchResult;
                }

                // Return our result
                return $matchResult;

            case 'quote_item_subscription_interval':
                // Check quote item attributes
                if ($object->getData('create_new_subscription_at_checkout') == '1') {
                    // This is a new subscription
                    return parent::validateAttribute($object->getData('new_subscription_interval'));
                }
                else if ($object->getData('item_fulfils_subscription') == '1') {
                    // This is a recurring order on a subscription
                    return parent::validateAttribute($object->getData('subscription_interval'));
                }
                else {
                    return false;
                }

            case 'quote_item_subscription_reorder_ordinal':
                // Check quote item attributes
                if ($object->getData('create_new_subscription_at_checkout') == '1') {
                    // This is a new subscription
                    $reorderOrdinal = 0;
                    return parent::validateAttribute($reorderOrdinal);
                }
                else if ($object->getData('item_fulfils_subscription') == '1') {
                    // This is a recurring order on a subscription
                    $reorderOrdinal = $object->getData('subscription_reorder_ordinal');
                    return parent::validateAttribute($reorderOrdinal);
                }
                else {
                    return false;
                }

            default:
                return parent::validate($object);
        }
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'quote_item_part_of_subscription':
                return 'select';

            case 'quote_item_subscription_interval':
                return 'string';

            case 'quote_item_subscription_reorder_ordinal':
                return 'string';

            default:
                return parent::getInputType();
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'quote_item_part_of_subscription':
                return 'select';

            case 'quote_item_subscription_interval':
                return 'text';

            case 'quote_item_subscription_reorder_ordinal':
                return 'text';

            default:
                return parent::getValueElementType();
        }
    }

    public function getValueSelectOptions()
    {
        switch ($this->getAttribute()) {
            case 'quote_item_part_of_subscription':
                return array(
                    array('value' => self::SUBSCRIPTION_STATUS_ANY, 'label' => Mage::helper('autoship')->__('Part of Subscription (New or Re-order)')),
                    array('value' => self::SUBSCRIPTION_STATUS_NEW, 'label' => Mage::helper('autoship')->__('Part of New Subscription Order')),
                    array('value' => self::SUBSCRIPTION_STATUS_REORDER, 'label' => Mage::helper('autoship')->__('Part of Subscription Re-order')),
                );

            default:
                return parent::getValueSelectOptions();
        }
    }

}
