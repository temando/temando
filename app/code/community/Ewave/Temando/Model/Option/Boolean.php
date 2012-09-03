<?php

/**
 * A boolean (yes/no) option.
 *
 * The action (specified by $this->_action_type) is applied to quotes if the
 * value is self::YES, otherwise no action is taken.
 *
 * @method Ewave_Temando_Model_Option_Boolean setYesAllowed
 * @method Ewave_Temando_Model_Option_Boolean setNoAllowed
 * @method boolean getYesAllowed
 * @method boolean getNoAllowed
 */
abstract class Ewave_Temando_Model_Option_Boolean extends Ewave_Temando_Model_Option_Abstract
{
    
    const YES = 'Y';
    const NO = 'N';
    
    protected function _setupValues()
    {
        $this->_values= array(
            self::YES => Mage::helper('temando')->__('Yes'),
            self::NO => Mage::helper('temando')->__('No'),
        );
    }
    
    /**
     * Only apply the action if the forced value is self::YES, or the value
     * is self::YES (as long as the forced value isn't self::NO).
     *
     * (non-PHPdoc)
     *
     * @see Ewave_Temando_Model_Option_Abstract::_apply()
     */
    protected function _apply($value, &$quote)
    {
        if (($value === self::YES && $this->getForcedValue() !== self::NO) || $this->getForcedValue() === self::YES) {
            $this->_action->apply($quote);
            return true;
        } else {
            return false;
        }
    }
    
}
