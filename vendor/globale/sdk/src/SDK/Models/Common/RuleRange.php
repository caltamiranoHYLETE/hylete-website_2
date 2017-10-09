<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common;

/**
 * Class RuleRange
 * @method getRangeBehavior()
 * @method getFrom()
 * @method getTo()
 * @method getLowerTarget()
 * @method getUpperTarget()
 * @method getThreshold()
 * @method getRoundingExceptions()
 * @method getTargetBehaviorHelperValue()
 * @method $this setRangeBehavior()
 * @method $this setFrom($From)
 * @method $this setTo($To)
 * @method $this setLowerTarget($LowerTarget)
 * @method $this setUpperTarget($UpperTarget)
 * @method $this setThreshold($Threshold)
 * @method $this setRoundingExceptions($RoundingExceptions)
 * @method $this setTargetBehaviorHelperValue($TargetBehaviorHelperValue)
 * @package GlobalE\SDK\Models\Common
 */
class RuleRange extends Common {

    // Range types
    /**
     * Absolute range target
     */
    const Absolute_Target = 1;
    /**
     * Relative range target
     */
    const Relative_Decimal_Target = 2;
    /**
     * Relative whole range target
     */
    const Relative_Whole_Target = 3;
    /**
     * Nearest range target
     */
    const Nearest_Target = 4;

    /**
     * Range behavior
     * @var string
     * @access public
     */
    public $RangeBehavior;
    /**
     * Target from range
     * @var string
     * @access public
     */
    public $From;
    /**
     * Target to range
     * @var string
     * @access public
     */
    public $To;
    /**
     * Lower target from range
     * @var string
     * @access public
     */
    public $LowerTarget;
    /**
     * Upper target from range
     * @var string
     * @access public
     */
    public $UpperTarget;
    /**
     * Range threshold
     * @var string
     * @access public
     */
    public $Threshold;
    /**
     * Rounding exceptions
     * @var array
     * @access public
     */
    public $RoundingExceptions = array();

    /**
     * Target behavior value
     * @var string
     * @access public
     */
    public $TargetBehaviorHelperValue;
}