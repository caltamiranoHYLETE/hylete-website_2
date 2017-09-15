<?php
/**
 *
 * @category   TBT
 * @package    TBT_Rewards
 * * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Helper_Debug_Catalogrule extends Mage_Core_Helper_Abstract 
{
    /**
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    public function disableAllRules() 
    {
        $this->enableOnlyAndReturn ( - 1 );
        return $this;
    }

    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    public function enableOnlyAndReturn($rule_id = 1) 
    {
        $rules = $this->getAllRules ();
        return $this->enableOnlyAndReturnInCollection($rules, $rule_id);
    }

    /**
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    public function enableOnlyAndReturnInCollection($rules, $rule_id = 1)
    {
        $active_rule = null;
        foreach ( $rules as $rule ) {
            if ($rule->getId () == $rule_id) {
                if (( int ) $rule->getIsActive () == 0) {
                    $active_rule = $rule->setIsActive ( 1 );
                    Mage::helper('rewards/debug')->printMessage("Enabling '{$rule->getName()}':[{$rule->getId()}].");
                    $rule->save ();
                }
            } elseif (( int ) $rule->getIsActive () == 1) {
                $rule->setIsActive ( 0 );
                Mage::helper('rewards/debug')->printMessage("Disabling '{$rule->getName()}':[{$rule->getId()}].");
                $rule->save ();
            }
        }

        return $active_rule;
    }

    /**
     *
     * @return TBT_Rewards_Model_Mysql4_Catalogrule_Rule_Collection
     */
    public function getAllRules() {
        return Mage::getModel ( 'rewards/catalogrule_rule' )->getPointsRuleCollection ( false );
    }
}
