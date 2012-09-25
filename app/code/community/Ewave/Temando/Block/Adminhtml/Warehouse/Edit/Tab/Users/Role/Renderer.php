<?php

/**
 * Custom renderer for user role
 *
 * @author martin
 */
class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Users_Role_Renderer 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row)
    {
	$value = $row->getData($this->getColumn()->getIndex());
	$role = Mage::getModel('admin/role')->load($value);
	
	if($role->getId() == $value) {
	    return '<span>'.$role->getRoleName().'</span>';
	}
	
	//just return whatever that is
	return parent::render($row);
    }
}


