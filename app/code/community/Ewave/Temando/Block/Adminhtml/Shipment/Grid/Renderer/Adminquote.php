<?php
/**
 * Custom grid renderer for admin selected quote description 
 */
class Ewave_Temando_Block_Adminhtml_Shipment_Grid_Renderer_Adminquote extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
    
    public function _getValue(Varien_Object $row)
    {
        $shipment = Mage::getModel('temando/shipment')->load($row->getId());
	$adminQuote = Mage::getModel('temando/quote')->load($shipment->getAdminSelectedQuoteId());
	/* @var $adminQuote Ewave_Temando_Model_Quote */
	
	if(!$adminQuote->getId()) {
	    return 'None selected';
	}
	
	foreach ($shipment->getOptions() as $_option) {
	    /* @var $_option Ewave_Temando_Model_Option_Abstract */
	    switch($_option->getId()) {
		case 'insurance':
		    if($_option->getForcedValue() === 'Y') {
			$adminQuote->setInsuranceIncluded(true);
		    }
		    break;
		case 'carbonoffset':
		    if($_option->getForcedValue() === 'Y') {
			$adminQuote->setCarbonIncluded(true);
		    }
		    break;
	    }
	}
	
	return $adminQuote->getAdminSelectedQuoteDescription(true);
    }   
}



