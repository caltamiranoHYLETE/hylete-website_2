<?php
namespace GlobalE\SDK\Core;

use GlobalE\SDK\Core;

/**
 * Class Validator
 * @package GlobalE\SDK\Models
 */
class Validator
{
    // TODO: PHPSDK-213

    /**
     * Will validate that the action & interface that user tries to access is exist, and passed arguments are correct.
     * @param string $Interface Name of the interface to validate(aka controller).
     * @param string $Action Name of the action(aka method) to validate.
     * @param array $GivenArgs arguments that user passed to action of some interface.
     * @throws \Exception
     */
    public static function Validate($Interface, $Action, array $GivenArgs = array()){

        // Will get array of rules for specified $Action in $Interface.
        $ActionRules = self::getValidatorRulesForActionInterface($Interface, $Action);

        // Will check rules of each argument in $ActionRules
        foreach($ActionRules as $ArgKey => $ArgRules){

            // If we miss this argument from $GivenArgs, and this is not optional argument, we will throw an exception.
            if(empty($GivenArgs[$ArgKey]) && empty($ArgRules['optional'])){
                self::logAndThrowException("Argument #$ArgKey is missing for action '$Action' in interface '$Interface'.");
            }
            elseif (empty($GivenArgs[$ArgKey])){
                continue;
            }

            // If expected argument is NOT an array, making it array, in order to work the same way in both cases(array & not array arguments).
            if(!isset($ArgRules['array']) || $ArgRules['array'] !== true){
                $GivenArgs[$ArgKey] = array($GivenArgs[$ArgKey]);
            }

            // If expected argument IS array, but the given is not, will throw an exception.
            elseif (!is_array($GivenArgs[$ArgKey])){
                self::logAndThrowException("Argument #$ArgKey for action '$Action' in interface '$Interface' expected to be array.");
            }

            foreach ($GivenArgs[$ArgKey] as $GivenArg){

                // If it's the common we expect, we need to check that the properties in the common are correct.
                if (is_object($GivenArg) && get_class($GivenArg) === $ArgRules['type']){

                    foreach ($ArgRules['properties'] as $PropertyName => $PropertyRules){

                        // If this is a common in a common, making one more step into, and checking also the inner common properties
                        if(isset($GivenArg->$PropertyName) && is_object($GivenArg->$PropertyName) && get_class($GivenArg->$PropertyName) === $PropertyRules['type']){

                            foreach ($PropertyRules['properties'] as $InnerPropertyName => $InnerPropertyRules){

                                // If the property from $InnerPropertyName not exists and not optional in the argument, or from different type, we will throw an exception
                                if((!isset($GivenArg->$PropertyName->$InnerPropertyName) && empty($InnerPropertyRules['optional'])) ||
                                    (isset($GivenArg->$PropertyName->$InnerPropertyName) && !self::isCorrectType($GivenArg->$PropertyName->$InnerPropertyName,$InnerPropertyRules['type']))){

                                    self::logAndThrowException("Inner common '".get_class($GivenArg->$PropertyName)."' of common '" . get_class($GivenArg) . "' in action '$Action' of interface '$Interface' ".
                                                               "expecting property '$InnerPropertyName' of type " . $InnerPropertyRules['type']. ".");
                                }
                            }
                        }

                        // If the property from $PropertyRules not exists and not optional, or from different type, we will throw an exception
                        elseif((!isset($GivenArg->$PropertyName) && empty($PropertyRules['optional'])) ||
                               (isset($GivenArg->$PropertyName) && !self::isCorrectType($GivenArg->$PropertyName,$PropertyRules['type']))){
                            self::logAndThrowException("Common '".get_class($GivenArg)."' in action '$Action' of interface '$Interface' expecting property '$PropertyName' " .
                                                       "of type " . $PropertyRules['type']. ".");
                        }
                    }
                }

               // If it's not the common we expect, and not the same argument type, we will throw an exception.
                elseif(!self::isCorrectType($GivenArg,$ArgRules['type'])){

                    self::logAndThrowException("Action '$Action' in interface '$Interface' expecting argument #$ArgKey" .
                                               " to be " . $ActionRules[$ArgKey]['type'] .", but got " .  gettype($GivenArg) . ".");
                }
            }
        }
    }


    /**
     * Will check if the type of var is correct by the validator rules.
     * @param mixed $GivenVar var that will be checked
     * @param string $RuleType the type that this var should have.
     * @return bool
     */
    private static function isCorrectType($GivenVar,$RuleType){

        // Return true if it's the same type as gettype() function returns
        if(gettype($GivenVar) === $RuleType){
            return true;
        }

        // Return true if the type is number and the given var is also a number.
        if($RuleType === 'number' && is_numeric($GivenVar)){
            return true;
        }

        return false;
    }

    /**
     * Will return array of rules for specified $Action in $Interface,
     * and throw exception if it's not allowed.
     * @param $Interface
     * @param $Action
     * @return array
     * @throws \Exception
     */
    private static function getValidatorRulesForActionInterface($Interface,$Action){

        // If it's not in the $ValidatorRules array, it means the action isn't allowed, and we will throw an exception.
        if(!isset(Validator\Rules::$ValidatorRules[$Interface][$Action])){
            self::logAndThrowException("Action '$Action' in interface '$Interface' is NOT allowed, or validation rules was not set.");
        }

        return Validator\Rules::$ValidatorRules[$Interface][$Action];
    }

    /**
     * @param $Msg
     * @throws \Exception
     */
    private static function logAndThrowException($Msg){
        Core\Log::log($Msg, Core\Log::LEVEL_ERROR);
        throw new \Exception($Msg);
    }
}