<?php
class Cminds_Coupon_Model_Mysql4_Coupon_Collection extends Mage_SalesRule_Model_Resource_Coupon_Collection
{
    protected function _construct()
    {
        parent::_construct();
    }

    public function addCountedErrors() {
        $this->getSelect()
            ->joinLeft($this->getTable('cminds_coupon/coupon_error_count') . ' AS c', 'main_table.coupon_id = c.coupon_id and main_table.rule_id = c.rule_id');
        return $this;
    }

    public function addRuleToFilter($rule)
    {
        if ($rule instanceof Mage_SalesRule_Model_Rule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int)$rule;
        }

        $this->addFieldToFilter('main_table.rule_id', $ruleId);

        return $this;
    }
}