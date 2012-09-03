<?php

class Ewave_Temando_Model_Shipping_Method
{
    
    public $_weightUnit;
    public $_distanceUnit;
    public $_debug = false;
    public $c;

    function __construct()
    {
        $this->c = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    function roundTo($val, $unit)
    {
        $newVal = $val;
        switch ($unit) {
            case "Kilograms":
                $newVal = (float) $val * 1000;
                break;
            case "Pounds":
                $newVal = (float) $val * 453.59237;
                break;
            case "Ounces":
                $newVal = (float) $val * 28.3495231;
                break;
            case "Metres":
                $newVal = (float) $val * 100;
                break;
            case "Inches":
                $newVal = (float) $val * 2.54;
                break;
            case "Feet":
                $newVal = (float) $val * 30.48;
                break;
        }
        if ((int) $newVal == 0) :
            return ceil($newVal);
        else :
            return (int) $newVal;
        endif;
    }

    public function fetchAllCarrier($carrierQuote, $validCarrier)
    {
        $result = array();
        if (count($carrierQuote) > 0) {
            foreach ($carrierQuote as $carreirQuotes) {
                foreach ($validCarrier as $key => $value) {
                    if ($key == $carreirQuotes->carrier->id) {
                        $result[$key] = array(
                            "carreirId" => $carreirQuotes->carrier->id,
                            "carrierName" => $carreirQuotes->carrier->companyName,
                            "currency" => $carreirQuotes->currency,
                            "deliveryMethod" => $carreirQuotes->deliveryMethod,
                            "etaFrom" => $carreirQuotes->etaFrom,
                            "etaTo" => $carreirQuotes->etaTo,
                            "guaranteedEta" => $carreirQuotes->guaranteedEta,
                            "lowestprice" => $carreirQuotes->totalPrice,
                            "basePrice" => $carreirQuotes->basePrice,
                            "tax" => $carreirQuotes->tax
                        );
                    }
                }
            }
            return $result;
        }
    }

    /** Calculation for valid lowest carrier and return type array * */
    public function fetchLowestCarrier($carrierQuote, $validCarrier)
    {
        $i = 0;
        $result = array();
        if (count($carrierQuote) > 0) {
            foreach ($carrierQuote as $carreirQuotes) {
                foreach ($validCarrier as $key => $value) {
                    if ($key == $carreirQuotes->carrier->id) {
                        if ($i == 0) {
                            $lowestPrice = $carreirQuotes->totalPrice;
                            $result[$key] = array(
                                "carreirId" => $carreirQuotes->carrier->id,
                                "carrierName" => $carreirQuotes->carrier->companyName,
                                "currency" => $carreirQuotes->currency,
                                "deliveryMethod" => $carreirQuotes->deliveryMethod,
                                "etaFrom" => $carreirQuotes->etaFrom,
                                "etaTo" => $carreirQuotes->etaTo,
                                "guaranteedEta" => $carreirQuotes->guaranteedEta,
                                "lowestprice" => $carreirQuotes->totalPrice,
                                "basePrice" => $carreirQuotes->basePrice,
                                "tax" => $carreirQuotes->tax
                            );
                        } elseif ($lowestPrice > $carreirQuotes->totalPrice) {
                            $result[$key] = array(
                                "carreirId" => $carreirQuotes->carrier->id,
                                "carrierName" => $carreirQuotes->carrier->companyName,
                                "currency" => $carreirQuotes->currency,
                                "deliveryMethod" => $carreirQuotes->deliveryMethod,
                                "etaFrom" => $carreirQuotes->etaFrom,
                                "etaTo" => $carreirQuotes->etaTo,
                                "guaranteedEta" => $carreirQuotes->guaranteedEta,
                                "lowestprice" => $carreirQuotes->totalPrice,
                                "basePrice" => $carreirQuotes->basePrice,
                                "tax" => $carreirQuotes->tax
                            );
                        }
                        $i++;
                    }
                }
            }
        }
        return $result;
    }

    /** Calculation for valid lowest carrier and return type array * */
    public function fetchLowestCarrierFront($carrierQuote)
    {
        $i = 0;
        if (count($carrierQuote) > 0) {
            foreach ($carrierQuote as $key => $val) {
                if ($i == 0) {
                    $lowestPrice = $val['lowestprice'];
                    $oldkey = $key;
                } elseif (
                        $lowestPrice > $val['lowestprice']) {
                    unset($carrierQuote[$oldkey]);
                    $lowestPrice = $val['lowestprice'];
                    $oldkey = $key;
                }
                $i++;
            }
        }
        return $carrierQuote;
    }

    /**  function for get quote by admin  DEAD FUCNTION ZONE DELETE THIS* */
    public function cartProductGetQuote($data)
    {
        $anithink = array();
        $anithink[] = array(
            "class" => $data['classsettings'],
            "subclass" => $data['subclass'],
            "packaging" => $data['packaging'],
            "quantity" => $data['qty'],
            "distanceMeasurementType" => $data['measureunit'],
            "weightMeasurementType" => $data['weightunit'],
            "weight" => $data['weight'],
            "length" => $data['length'],
            "width" => $data['width'],
            "height" => $data['height'],
            "qualifierFreightGeneralFragile" => $data['qualifierFreightGeneralFragile']
        );
        return $anithink;
    }

    public function destination($desCountry, $topcode, $toCity, $toRegion, $toStreet, $toCustomer, $toCompany)
    {
        return array(
            "contactName" => $toCustomer,
            "companyName" => $toCompany,
            "street" => $toStreet,
            "suburb" => $toCity,
            "state" => $toRegion,
            "code" => $topcode,
            "country" => $desCountry,
            "phone1" => "(02) 9333 3333",
            "phone2" => "",
            "fax" => "",
            "email" => "jane.doe@yahoo.com.au"
        );
    }

    public function origin($id)
    {
        $record0 = $this->c->query("SELECT * from temando_warehouse where warehouse_id=$id");
        $warehouseRecord = $record0->fetch(PDO::FETCH_ASSOC);
        return array(
            "contactName" => $warehouseRecord["cperson"],
            "companyName" => $warehouseRecord["title"],
            "street" => $warehouseRecord["address"],
            "suburb" => $warehouseRecord["city"],
            "state" => $warehouseRecord["state"],
            "code" => $warehouseRecord["zip"],
            "country" => $warehouseRecord["country"],
            "phone1" => $warehouseRecord["phone"],
            "phone2" => $warehouseRecord["phone2"],
            "fax" => $warehouseRecord["fax"],
            "email" => $warehouseRecord["email"]
        );
    }

    public function getCarrier()
    {
        $rslt = $this->c->query("select * from temando_carrierinfo");
        $carrier = array();
        while ($row = $rslt->fetch(PDO::FETCH_ASSOC)) {
            $carrier[$row['carrier_id']] = $row['company_name'];
        }
        return $carrier;
    }

    public function getFinalCarrierPrice($c, $finalPrice)
    {
        foreach ($c as $key => $val) {
            $total = 0;
            foreach ($finalPrice as $k => $v) {
                foreach ($v as $e => $d) {
                    if ($key == $e) {
                        $total += $d["lowestprice"];
                    }
                }
            }
            $c[$key]['lowestprice'] = $total;
        }
        return $c;
    }
    
}
