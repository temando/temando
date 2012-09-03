<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Form_Quotes extends Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Abstract
{
    
    protected $_cheapest_quote_id;
    protected $_fastest_quote_id;
    protected $_customer_selected_quote_id;
    protected $_admin_selected_quote_id;
    
    public function __construct() {
        parent::__construct();
        if (($quotes = $this->getQuotes()->load())) {
            if(($cheapest_quote = $quotes->getCheapest())) {
                $this->_cheapest_quote_id = $cheapest_quote->getId();
            }
            if(($fastest_quote = $quotes->getFastest())) {
                $this->_fastest_quote_id = $fastest_quote->getId();
            }
        }
    
        if ($this->getShipment()) {
            $this->_customer_selected_quote_id = $this->getShipment()->getCustomerSelectedQuoteId();
	    $this->_admin_selected_quote_id = $this->getShipment()->getAdminSelectedQuoteId();
        }
    }
    
    public function formatQuotePrice(Ewave_Temando_Model_Quote $quote)
    {
        return
            $quote->getCurrency() . ' ' .
            $this->formatCurrency($quote->getTotalPrice()) .
            ' (inc. ' .
            $this->formatCurrency($quote->getTax()) .
            ' tax)';
    }
    
    public function getQuoteNotes(Ewave_Temando_Model_Quote $quote)
    {
        $text = '';
        
        if ($this->_cheapest_quote_id == $quote->getId()) {
            $text .= 'Cheapest';
        }
        
        if ($this->_fastest_quote_id == $quote->getId()) {
            if ($text) {
                $text .= ', ';
            }
            $text .= 'Fastest';
        }
        
        if ($this->_customer_selected_quote_id == $quote->getId()) {
            if ($text) {
                $text .= ', ';
            }
            $text .= 'Customer Selected';
        }
	
	if ($this->_admin_selected_quote_id == $quote->getId()) {
            if ($text) {
                $text .= ', ';
            }
            $text .= 'Admin Selected';
        }	
        
        return $text;
    }
    
}
