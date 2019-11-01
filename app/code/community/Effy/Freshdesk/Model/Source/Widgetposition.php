<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Model_Source_Widgetposition
 */
class Effy_Freshdesk_Model_Source_Widgetposition extends Effy_Freshdesk_Model_Source_Abstract
{
    const POSITION_TOP    = 1;
    const POSITION_RIGHT  = 2;
    const POSITION_BOTTOM = 3;
    const POSITION_LEFT   = 4;

    static $POSITIONS = array(
        self::POSITION_LEFT => 'Left',
        self::POSITION_RIGHT => 'Right',
        self::POSITION_TOP => 'Top',
        self::POSITION_BOTTOM => 'Bottom',
    );

    public function toOptionArray()
    {
        $return = array();
        foreach (self::$POSITIONS as $value => $label) {
            $return[] = array('value' => $value, 'label' => $this->_getHelper()->__($label));
        }

        return $return;
    }
}
