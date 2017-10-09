<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK\Models\Common\RoundingException;
use GlobalE\SDK\API;

/**
 * Class Rounding
 * @package GlobalE\SDK\Models
 */
class Rounding {

    /**
     * Price amount absolute value
     * @var float
     * @access private
     */
    private $price;

    /**
     * Rule object
     * @var API\Common\Response\RoundingRule
     * @access private
     */
    private $rule;

	/**
	 * if Price is negative
	 * @var bool
	 */
	private $Negative = false;

    /**
     * Rounding constructor.
     * @param float $price
     * @param API\Common\Response\RoundingRule $rule
     * @access public
     */
    public function __construct($price,$rule){
        $this->price = abs($price);
        $this->rule = $rule;

		if($price < 0 ){
			$this->Negative = true;
		}
    }

    /**
     * Initialize rounding rules
     * @return float
     * @access public
     */
    public function round(){
    	//in the case of price = 0
    	if($this->price == 0 ){
			return 0;
		}

        $range = $this->getCompatibleRange();
        if(!empty($range)){
            $range = $this->convertRangeToAbsolute($range);
            $round_price = $this->absoluteTargetRounding($range);
        }
        else{
            $round_price = $this->price;
        }

		if($this->Negative){
			$round_price = -1 * $round_price;
		}
        return $round_price;
    }

    /**
     * Get compatible range
     * @return \stdClass
     * @access public
     */
    private function getCompatibleRange(){

        $compatible_range = null;
        foreach($this->rule->RoundingRanges as $range){
            if($range->From < $this->price && $this->price <= $range->To){
                $compatible_range = $range;
                break;
            }
        }

        return $compatible_range;
    }

    /**
     * Change the price according to the absolute rounding
     * @param Common\RuleRange $range
     * @return float
     * @access private
     */
    private function absoluteTargetRounding(Common\RuleRange $range){
        foreach($range->getRoundingExceptions() as $ex){
            /* @var $ex RoundingException */
            if($this->price == $ex->getExceptionValue()){
                return $this->price;
            }
        }

        if($this->price < $range->getThreshold()){
            $round_price = $range->getLowerTarget();
        }
        else {
            $round_price = $range->getUpperTarget();
        }

        return $round_price;
    }

    /**
     * Converts rounding range of any type to absolute type
     * @param object $range
     * @return Common\RuleRange
     * @access private
     */
    private function convertRangeToAbsolute($range){

        $absolute_range = new Common\RuleRange();

        // No rounding logic here, only setting it to Common\RuleRange object
        if($range->RangeBehavior == Common\RuleRange::Absolute_Target){
            $absolute_range->setLowerTarget($range->LowerTarget);
            $absolute_range->setUpperTarget($range->UpperTarget);
            $absolute_range->setThreshold($range->Threshold);

            $rounding_exceptions = array();
            foreach($range->RoundingExceptions as $ex){
                $exception = new RoundingException();
                $exception->setExceptionValue($ex->ExceptionValue);
                $rounding_exceptions[] = $exception;
            }
            $absolute_range->setRoundingExceptions($rounding_exceptions);
        }

        elseif($range->RangeBehavior == Common\RuleRange::Relative_Decimal_Target){
            $price_integer = floor($this->price);
            $absolute_range->setLowerTarget($price_integer - 1 + $range->LowerTarget);
            $absolute_range->setUpperTarget($price_integer + $range->UpperTarget);
            $absolute_range->setThreshold($price_integer + $range->Threshold);

            $rounding_exceptions = $this->formatRoundingExceptions($range->RoundingExceptions,$price_integer);
            $absolute_range->setRoundingExceptions($rounding_exceptions);
        }

        elseif ($range->RangeBehavior == Common\RuleRange::Relative_Whole_Target){
            if($range->TargetBehaviorHelperValue == 0){
                $range->TargetBehaviorHelperValue = 10;
            }
            $base = floor($this->price/$range->TargetBehaviorHelperValue)*$range->TargetBehaviorHelperValue;
            $absolute_range->setLowerTarget($base - $range->TargetBehaviorHelperValue + $range->LowerTarget);
            $absolute_range->setUpperTarget($base + $range->UpperTarget);
            $absolute_range->setThreshold($base + $range->Threshold);

            $rounding_exceptions = $this->formatRoundingExceptions($range->RoundingExceptions,$base);
            $absolute_range->setRoundingExceptions($rounding_exceptions);
        }

        elseif ($range->RangeBehavior == Common\RuleRange::Nearest_Target){
            if($range->TargetBehaviorHelperValue == 0){
                $range->TargetBehaviorHelperValue = 5;
            }
            $base = floor($this->price/$range->TargetBehaviorHelperValue)*$range->TargetBehaviorHelperValue;
            $absolute_range->setLowerTarget($base - 1 + $range->LowerTarget);
            $absolute_range->setUpperTarget($base - 1 + $range->TargetBehaviorHelperValue + $range->UpperTarget);
            $absolute_range->setThreshold($base + $range->Threshold);

            $rounding_exceptions = $this->formatRoundingExceptions($range->RoundingExceptions,$base);
            $absolute_range->setRoundingExceptions($rounding_exceptions);
        }

        return $absolute_range;
    }

    /**
     * Exceptions for rounding format
     * @param array $exceptions
     * @param $addition
     * @return RoundingException[]
     * @access private
     */
    private function formatRoundingExceptions($exceptions,$addition){
        $rounding_exceptions = array();
        foreach($exceptions as $ex){
            $exception = new RoundingException();
            $exception->setExceptionValue($ex->ExceptionValue+$addition);
            $rounding_exceptions[] = $exception;
        }
        return $rounding_exceptions;
    }


}