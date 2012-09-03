<?php

abstract class Ewave_Temando_Model_System_Config_Source_Unit extends Ewave_Temando_Model_System_Config_Source
{
    
    /**
     * Same as the parent::_options array, except with brief labels as values.
     *
     * As the $_options array will contain the name of the unit (e.g. "Metres",
     * "Inches"), this will contain a shorter description e.g. the units when
     * used after a measurement ("m.", "in.", etc).
     *
     * @var array
     */
    protected $_brief_options;
    
    public function __construct()
    {
        parent::__construct();
        $this->_setupBriefOptions();
    }
    
    /**
     * Sets up the $_brief_options array with the correct values.
     *
     * This function is called in the constructor.
     *
     * @return Ewave_Temando_Model_System_Config_Source_Abstract
     */
    protected abstract function _setupBriefOptions();
    
    /**
     * Looks up an option by key and gets the label.
     *
     * @param mixed $value
     * @return mixed
     */
    public function getBriefOptionLabel($value)
    {
        if (array_key_exists($value, $this->_brief_options)) {
            return $this->_brief_options[$value];
        }
        return null;
    }
    
    public function toBriefOptionArray()
    {
        return $this->_toOptionArray($this->_brief_options);
    }
    
}
