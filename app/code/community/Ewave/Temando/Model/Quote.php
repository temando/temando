<?php

/**
 * @method Ewave_Temando_Model_Quote setGenerated()
 * @method Ewave_Temando_Model_Quote setAccepted()
 * @method Ewave_Temando_Model_Quote setTotalPrice()
 * @method Ewave_Temando_Model_Quote setBasePrice()
 * @method Ewave_Temando_Model_Quote setTax()
 * @method Ewave_Temando_Model_Quote setCurrency()
 * @method Ewave_Temando_Model_Quote setDeliveryMethod()
 * @method Ewave_Temando_Model_Quote setUsingGeneralRail()
 * @method Ewave_Temando_Model_Quote setUsingGeneralRoad()
 * @method Ewave_Temando_Model_Quote setUsingGeneralSea()
 * @method Ewave_Temando_Model_Quote setUsingExpressAir()
 * @method Ewave_Temando_Model_Quote setUsingExpressRoad()
 * @method Ewave_Temando_Model_Quote setEtaFrom()
 * @method Ewave_Temando_Model_Quote setEtaTo()
 * @method Ewave_Temando_Model_Quote setGuaranteedEta()
 * @method Ewave_Temando_Model_Quote setLoaded()
 * @method Ewave_Temando_Model_Quote setInsuranceIncluded()
 *
 * @method string getGenerated()
 * @method string getAccepted()
 * @method string getTotalPrice()
 * @method string getBasePrice()
 * @method string getTax()
 * @method string getCurrency()
 * @method string getDeliveryMethod()
 * @method string getUsingGeneralRail()
 * @method string getUsingGeneralRoad()
 * @method string getUsingGeneralSea()
 * @method string getUsingExpressAir()
 * @method string getUsingExpressRoad()
 * @method string getEtaFrom()
 * @method string getEtaTo()
 * @method string getGuaranteedEta()
 * @method string getLoaded()
 * @method boolean getInsuranceIncluded()
 */
class Ewave_Temando_Model_Quote extends Mage_Core_Model_Abstract
{
    
    protected $_carrier = null;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/quote');
    }
    
    public function __clone()
    {
        $this->_carrier = clone $this->getCarrier();
    }
    
    /**
     * Sets the carrier providing this quote.
     *
     * @param <type> $carrier_id
     *
     * @return Ewave_Temando_Model_Quote
     */
    public function setCarrier($carrier_id)
    {
        $carrier = Mage::getModel('temando/carrier')
            ->load($carrier_id);
            
        if ($carrier->getId() == $carrier_id) {
            // exists in the database
            $this->_carrier = $carrier;
            $this->setData('carrier_id', $carrier_id);
        }
        return $this;
    }
    
    /**
     * Gets the carrier providing this quote.
     *
     * @return Ewave_Temando_Model_Carrier
     */
    public function getCarrier()
    {
        if (!$this->_carrier) {
            $this->setCarrier($this->getCarrierId());
        }
        return $this->_carrier;
    }
    
    /**
     * Loads values into this object from a
     *
     * @param stdClass $response the SOAP response directly from the Temando
     * API.
     */
    public function loadResponse(stdClass $response, $packaging = null)
    {
        if ($response instanceof stdClass) {
            $carrier = Mage::getModel('temando/carrier')
                ->load($response->carrier->id, 'carrier_id');
            /* @var $carrier Ewave_Temando_Model_Carrier */
                
            $carrier
                ->setCarrierId(isset($response->carrier->id) ? $response->carrier->id : '')
                ->setCompanyName(isset($response->carrier->companyName) ? $response->carrier->companyName : '')
                ->setCompanyContact(isset($response->carrier->companyContact) ? $response->carrier->companyContact : '')
                ->setStreetAddress(isset($response->carrier->streetAddress) ? $response->carrier->streetAddress : '')
                ->setStreetSuburb(isset($response->carrier->streetSuburb) ? $response->carrier->streetSuburb : '')
                ->setStreetCity(isset($response->carrier->streetCity) ? $response->carrier->streetCity : '')
                ->setStreetState(isset($response->carrier->streetState) ? $response->carrier->streetState : '')
                ->setStreetPostcode(isset($response->carrier->streetCode) ? $response->carrier->streetCode : '')
                ->setStreetCountry(isset($response->carrier->streetCountry) ? $response->carrier->streetCountry : '')
                ->setPostalAddress(isset($response->carrier->postalAddress) ? $response->carrier->postalAddress : '')
                ->setPostalSuburb(isset($response->carrier->postalSuburb) ? $response->carrier->postalSuburb : '')
                ->setPostalCity(isset($response->carrier->postalCity) ? $response->carrier->postalCity : '')
                ->setPostalState(isset($response->carrier->postalState) ? $response->carrier->postalState : '')
                ->setPostalPostcode(isset($response->carrier->postalCode) ? $response->carrier->postalCode : '')
                ->setPostalCountry(isset($response->carrier->postalCountry) ? $response->carrier->postalCountry : '')
                ->setPhone(isset($response->carrier->phone1) ? $response->carrier->phone1 : '')
                ->setEmail(isset($response->carrier->email) ? $response->carrier->email : '')
                ->setWebsite(isset($response->carrier->website) ? $response->carrier->website : '')
                ->save();    // save new details back to the database
            
            $extras = $response->extras->extra;
            if (!is_array($extras)) {
                $extras = array($extras);
            }
            
            $extras_array = array();
            
            foreach ($extras as $extra) {
                $extra_id = trim(strtolower($extra->summary));
                $extra_id = str_replace(' ', '', $extra_id);
                $extras_array[$extra_id] = array(
                    'summary'    => $extra->summary,
                    'details'    => $extra->details,
                    'totalPrice' => $extra->totalPrice,
                    'basePrice'  => $extra->basePrice,
                    'tax'        => $extra->tax,
                );
            }
            
            $this
                ->setCarrier($carrier->getId())
                ->setAccepted($response->accepted == 'Y')
                ->setTotalPrice($response->totalPrice)
                ->setBasePrice($response->basePrice)
                ->setTax($response->tax)
                ->setCurrency($response->currency)
                ->setDeliveryMethod($response->deliveryMethod)
                ->setEtaFrom($response->etaFrom)
                ->setEtaTo($response->etaTo)
                ->setGuaranteedEta($response->guaranteedEta == 'Y')
                ->setExtras($extras_array)
                ->setCarbonTotalPrice(array_key_exists('carbonoffset', $extras_array) ? $extras_array['carbonoffset']['totalPrice'] : 0)
                ->setInsuranceTotalPrice(array_key_exists('insurance', $extras_array) ? $extras_array['insurance']['totalPrice'] : 0)
                ->setPackaging($packaging)
                ->setLoaded(true);
        }
        return $this;
    }
    
    public function toBookingRequestArray($options)
    {
        $extras = $this->getExtras();
        
        if (isset($options['insurance']) && ($options['insurance'] === 'Y')) {
            $insurance = $extras['insurance'];
        } else {
            $insurance = false;
        }
        
        if (isset($options['carbonoffset']) && ($options['carbonoffset'] === 'Y')) {
            $carbon = $extras['carbonoffset'];
        } else {
            $carbon = false;
        }
        
        $request = array(
            'totalPrice'     => $this->getTotalPrice(),
            'basePrice'      => $this->getBasePrice(),
            'tax'            => $this->getTax(),
            'currency'       => $this->getCurrency(),
            'deliveryMethod' => $this->getDeliveryMethod(),
            'etaFrom'        => $this->getEtaFrom(),
            'etaTo'          => $this->getEtaTo(),
            'guaranteedEta'  => $this->getGuaranteedEta() ? 'Y' : 'N',
            'carrierId'      => $this->getCarrier()->getCarrierId(),
        );
        
        if ($carbon || $insurance) {
            $request['extras'] = array();
            $request['extras']['extra'] = array();
        }
        
        if ($carbon) {
            $request['extras']['extra'][] = $carbon;
        }
        if ($insurance) {
            $request['extras']['extra'][] = $insurance;
        }
        
        return $request;
    }
    
    public function setExtras($extras)
    {
        $this->setData('extras', serialize($extras));
        return $this;
    }
    
    public function getExtras()
    {
        if ($this->getData('extras')) {
            return unserialize($this->getData('extras'));
        }
        return null;
    }
    
    public function getDescription($show_carrier = true)
    {
        $carrier = $this->getCarrier();
        $title = '';
        
        if ($show_carrier) {
            $title .= $carrier->getCompanyName() . ' - ';
        }
        
        $title .= $this->getDeliveryMethod() . ' [' . $this->getEtaDescription() . $this->getExtraTitle();
        
        $title .= ']';
        
        return $title;
    }
    
    public function getTotalPriceIncSelectedExtras()
    {
	$price = $this->getTotalPrice();
	if($this->getInsuranceIncluded()) {
	    $price += $this->getInsuranceTotalPrice();
	}
	if($this->getCarbonIncluded()) {
	    $price += $this->getCarbonTotalPrice();
	}
	
	return $price;
	
    }
    
    public function getAdminSelectedQuoteDescription($show_carrier = true)
    {
	$price = $this->getTotalPrice();
	if($this->getInsuranceIncluded()) {
	    $price += $this->getInsuranceTotalPrice();
	}
	if($this->getCarbonIncluded()) {
	    $price += $this->getCarbonTotalPrice();
	}
	$formatted = Mage::app()->getLocale()->currency($this->getCurrency())->toCurrency($price);
	
	return $formatted . ' - ' . $this->getDescription($show_carrier);
    }

    public function getExtraTitle()
    {
        $title = '';
        if ($this->getInsuranceIncluded()) {
            $title .= ', includes insurance';
        }
        if ($this->getCarbonIncluded()) {
            if ($this->getInsuranceIncluded()) {
                $title .= ' and ';
            } else {
                $title .= ', includes ';
            }
            $title .= ' carbon offset';
        }

        return $title;
    }
    
    public function getEtaDescription()
    {
        $title = $this->getEtaFrom();
        
        if ($this->getEtaFrom() != $this->getEtaTo()) {
            $title .= ' - ' . $this->getEtaTo();
        }
        
        $title .= ' day';
        
        if ($this->getEtaTo() > 1) {
            $title .= 's';
        }
        
        return $title;
    }
    
    /**
     * Returns the average ETA.
     *
     * e.g. for an ETA of 1-4 days, the average is 2.5 days.
     */
    public function getAverageEta()
    {
        return ($this->getEtaFrom() + $this->getEtaTo()) / 2;
    }
    
    
    public function processAdjustment($adjustment, $value)
    {
	$helper = Mage::helper('temando');
	/* @var $helper Ewave_Temando_Helper_Data */
	
	$price = $this->getTotalPrice();
	switch($adjustment) {
		case Ewave_Temando_Model_System_Config_Source_Rule_Action_Adjustment_Type::MARKUP_FIXED:
		    $this->setTotalPrice($price + $value);
		    break;
		case Ewave_Temando_Model_System_Config_Source_Rule_Action_Adjustment_Type::MARKUP_PERCENT:
		    $this->setTotalPrice(round($price + ($price * ($value/100)), 2));
		    break;
		case Ewave_Temando_Model_System_Config_Source_Rule_Action_Adjustment_Type::SUBSIDY_FIXED:
		    $this->setTotalPrice($price - $value);
		    break;
		case Ewave_Temando_Model_System_Config_Source_Rule_Action_Adjustment_Type::SUBSIDY_PERCENT:
		    $this->setTotalPrice(round($price - ($price * ($value/100)), 2));
		    break;
		case Ewave_Temando_Model_System_Config_Source_Rule_Action_Adjustment_Type::MINMAX:
		    $range = explode(':', $value);
		    if(count($range) == 2) {
			if($price < $range[0]) {
			    $this->setTotalPrice($range[0]);
			} else if($price > $range[1]) {
			    $this->setTotalPrice($range[1]);
			}
		    }
		    break;
		case Ewave_Temando_Model_System_Config_Source_Rule_Action_Adjustment_Type::CAPPED:
		    $this->setTotalPrice($value);
		    break;
	}
	
	return $this;
    }
}
