<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;

/**
 * Class RoundingTest
 * @package GlobalE\Test\SDK\Models
 */
class RoundingTest extends \PHPUnit_Framework_TestCase {

    /**
     * Will test public method round of GlobalE\SDK\Model\Rounding class
     * for absolute target range
     * @dataProvider providerRoundAbsoluteTarget
     * @param $price
     * @param $round_price
     */
    public function testRoundAbsoluteTarget($price,$round_price){

        $rule = new \stdClass();
        $rule->RoundingRanges = array();
        $rule->RoundingRanges[0] = (object) $this->getAbsoluteTargetRange();
        $rounding_model = new Models\Rounding($price,$rule);

        $actual = $rounding_model->round();
        $expected = $round_price;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Will test public method round of GlobalE\SDK\Model\Rounding class
     * for relative decimal target range
     * @dataProvider providerRoundRelativeDecimalTarget
     * @param $price
     * @param $round_price
     */
    public function testRoundRelativeDecimalTarget($price,$round_price){

        $rule = new \stdClass();
        $rule->RoundingRanges = array();
        $rule->RoundingRanges[0] = (object) $this->getRelativeDecimalTargetRange();
        $rounding_model = new Models\Rounding($price,$rule);

        $actual = $rounding_model->round();
        $expected = $round_price;
        $this->assertEquals($expected, $actual);
    }

    /**
 * Will test public method round of GlobalE\SDK\Model\Rounding class
 * for relative whole target range
 * @dataProvider providerRoundRelativeWholeTarget
 * @param $price
 * @param $round_price
 */
    public function testRelativeWholeTarget($price,$round_price){

        $rule = new \stdClass();
        $rule->RoundingRanges = array();
        $rule->RoundingRanges[0] = (object) $this->getRelativeWholeTargetRange();
        $rounding_model = new Models\Rounding($price,$rule);

        $actual = $rounding_model->round();
        $expected = $round_price;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Will test public method round of GlobalE\SDK\Model\Rounding class
     * for nearest target range
     * @dataProvider providerRoundNearestTarget
     * @param $price
     * @param $round_price
     */
    public function testNearestTarget($price,$round_price){

        $rule = new \stdClass();
        $rule->RoundingRanges = array();
        $rule->RoundingRanges[0] = (object) $this->getRelativeNearestTargetRange();
        $rounding_model = new Models\Rounding($price,$rule);

        $actual = $rounding_model->round();
        $expected = $round_price;
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function providerRoundAbsoluteTarget(){
        return array(
            array(1.345,1),
            array(1.78,3),
            array(0.5,0.5),
            array(0.75,0.75),
            array(0.76,1)
        );
    }

    /**
     * @return array
     */
    public function providerRoundRelativeDecimalTarget(){
        return array(
            array(4.11,3.95),
            array(4.81,4.99),
            array(7.5,7.5),
            array(9.75,9.75),
            array(10,9.95)
        );
    }

    /**
     * @return array
     */
    public function providerRoundRelativeWholeTarget(){
        return array(
            array(807,810),
            array(505,499),
            array(104,99),
            array(303,299),
            array(709,710)
        );
    }

    /**
     * @return array
     */
    public function providerRoundNearestTarget(){
        return array(
            array(122.26,124.99),
            array(122.25,119.99),
            array(127.26,129.99),
            array(121.5,121.5),
            array(127.5,127.5)
        );
    }

    /**
     * @return array
     */
    private function getAbsoluteTargetRange(){
        return array(
            'RangeBehavior' => Common\RuleRange::Absolute_Target,
            'From' => 0,
            'To' => 3,
            'Threshold' => 1.77,
            'LowerTarget' => 1,
            'UpperTarget' => 3,
            'RoundingExceptions' =>
                array (
                    (object) array('ExceptionValue' => 0.5),
                    (object) array('ExceptionValue' => 0.75),
                ),
        );
    }

    /**
     * @return array
     */
    private function getRelativeDecimalTargetRange(){
        return array(
            'RangeBehavior' => Common\RuleRange::Relative_Decimal_Target,
            'From' => 3,
            'To' => 10,
            'Threshold' => 0.77,
            'LowerTarget' => 0.95,
            'UpperTarget' => 0.99,
            'RoundingExceptions' =>
                array (
                    (object) array('ExceptionValue' => 0.5),
                    (object) array('ExceptionValue' => 0.75),
                ),
        );
    }

    /**
     * @return array
     */
    private function getRelativeWholeTargetRange(){
        return array(
            'RangeBehavior' => Common\RuleRange::Relative_Whole_Target,
            'From' => 10,
            'To' => 1000,
            'Threshold' => 7,
            'LowerTarget' => 9,
            'UpperTarget' => 10,
            'TargetBehaviorHelperValue' => 10,
            'RoundingExceptions' =>
                array (
                ),
        );
    }

    /**
     * @return array
     */
    private function getRelativeNearestTargetRange(){
        return array(
            'RangeBehavior' => Common\RuleRange::Nearest_Target,
            'From' => 100,
            'To' => 1000,
            'Threshold' => 2.26,
            'LowerTarget' => 0.99,
            'UpperTarget' => 0.99,
            'TargetBehaviorHelperValue' => 5,
            'RoundingExceptions' =>
                array (
                    (object) array('ExceptionValue' => 1.5),
                    (object) array('ExceptionValue' => 2.5),
                ),
        );
    }
}