<?php

class Ewave_Temando_Adminhtml_ShipmentController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
	$this->loadLayout()
		->_setActiveMenu('temando/shipment')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Shipment Manager'), Mage::helper('adminhtml')->__('Shipment Manager'))
		->renderLayout();
    }

    public function editAction() {
	$id = $this->getRequest()->getParam('id');
	$shipment = Mage::getModel('temando/shipment')->load($id);
	/* @var $shipment Ewave_Temando_Model_Shipment */

	if ($shipment->getId()) {
	    $notices = array();
	    /* if (!$shipment->getReadyDate() || !$shipment->getReadyTime()) {
	      $notices[] = 'Quotes cannot be refreshed until a pick-up date and time have been specified.';
	      } */
	    if (!count($shipment->getBoxes())) {
		$notices[] = 'Quotes cannot be refreshed until at least one box is added to the shipment.';
	    }

	    foreach ($notices as $notice) {
		$this->_getSession()->addNotice($this->__($notice));
	    }

	    $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
	    if (!empty($data)) {
		$shipment->addData($data);
	    }

	    Mage::register('temando_shipment_data', $shipment);

	    $this->loadLayout()
		    ->_setActiveMenu('temando/shipment');

	    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

	    $this->renderLayout();
	} else {
	    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temando')->__('Shipment does not exist.'));
	    $this->_redirect('*/*/');
	}
    }

    public function saveAction() {
	if ($data = $this->getRequest()->getPost()) {
	    $shipment = Mage::getModel('temando/shipment');
	    /* @var $shipment Ewave_Temando_Model_Shipment */

	    $result = $this->_validateFormData($data);
	    if (isset($data['insurance'])) {
		Mage::getSingleton('adminhtml/session')->setData('insurance_' . $this->getRequest()->getParam('id'), $data['insurance']);
	    } else {
		Mage::getSingleton('adminhtml/session')->unsetData('insurance_' . $this->getRequest()->getParam('id'));
	    }

	    foreach ($result['notices'] as $notice) {
		$this->_getSession()->addNotice($notice);
	    }

	    if ($result['errors']) {
		$this->_getSession()
			->addError('Validation errors:')
			->setFormData($data);
		foreach ($result['errors'] as $error) {
		    $this->_getSession()->addError($error);
		}
		$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		return;
	    }

	    try {
		if (!$data['ready']) {
		    $data['ready_date'] = null;
		}
		$shipment->setId($this->getRequest()->getParam('id'))
			->addData($data);

		if ($data['boxes_deleted']) {
		    $box_ids = explode(',', $data['boxes_deleted']);
		    foreach ($box_ids as $box_id) {
			$box = Mage::getModel('temando/box');
			/* @var $box Ewave_Temando_Model_Box */
			$box->load($box_id);
			if ($box->getId()) {
			    $box->delete();
			}
		    }
		}

		if (isset($data['box']) && is_array($data['box'])) {
		    foreach ($data['box'] as $box_data) {
			$box = Mage::getModel('temando/box');
			/* @var $box Ewave_Temando_Model_Box */
			if (isset($box_data['id'])) {
			    $box->load($box_data['id']);
			}

			$box->setShipmentId($shipment->getId())
				->addData($box_data)
				->save();
		    }
		}

		$shipment
			->clearQuotes()
			->save();

		$shipment = Mage::getModel('temando/shipment')->load($shipment->getId());

		if (/* $shipment->getReadyDate() && $shipment->getReadyTime() && */ $shipment->getBoxes()) {
		    $shipment->fetchQuotes(
			    Mage::helper('temando')->getConfigData('general/username'), Mage::helper('temando')->getConfigData('general/password'), Mage::helper('temando')->getConfigData('general/sandbox')
		    );
		}

		$this->_getSession()->addSuccess($this->__('The shipment data has been saved.'));

		switch ($this->getRequest()->getParam('and')) {
		    case 'getquotes':
			$this->_redirect('*/*/edit', array('id' => $shipment->getId()));
			break;
		    case 'next':
		    // TODO: save and next
		    default:    // back to index
			$this->_redirect('*/*/');
			break;
		}
	    } catch (Exception $ex) {
		$this->_getSession()
			->addError($ex->getMessage())
			->setFormData($data);
		$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
	    }
	    return;
	}

	// nothing to save
	$this->_redirect('*/*/');
    }

    protected function _validateFormData(&$data) {
	$return = array(
	    'notices' => array(),
	    'errors' => array(),
	);

	if ($data['ready_date']) {
	    $timestamp = strtotime($data['ready_date']);
	    if ($timestamp) {
		$data['ready_date'] = date('Y-m-d', $timestamp);
	    } else {
		$return['errors'][] = 'Pick-up date is not a valid date.';
	    }
	}

	if (strtotime($data['ready_date'] . ' + 1 day') < date('U')) {
	    $return['notices'][] = 'Pick-up date is in the past.';
	    unset($data['ready_date']);
	}

	if (!in_array($data['ready_time'], array('AM', 'PM', ''))) {
	    $return['errors'][] = 'Pick-up time is not a valid selection.';
	}

	if (Mage::helper('temando')->isVersion2()) {
	    if (!isset($data['warehouse_id']) || empty($data['warehouse_id'])) {
		$return['errors'][] = 'Origin location not selected';
	    }
	}

	return $return;
    }

    public function bookAction() {
	$shipment_id = $this->getRequest()->getParam('shipment');
	$shipment = Mage::getModel('temando/shipment')
		->load($shipment_id);
	/* @var $shipment Ewave_Temando_Model_Shipment */

	$quote_id = $this->getRequest()->getParam('quote');
	$quote = Mage::getModel('temando/quote')
		->load($quote_id);
	/* @var $quote Ewave_Temando_Model_Quote */

	$error = null;

	if (!$shipment->getId()) {
	    $error = 'Shipment does not exist.';
	} else {
	    if (!$quote->getId()) {
		$error = 'Selected quote does not exist.';
	    } else {
		foreach ($shipment->getOptions() as $o) {
		    if (("insurance" == $o->getId()) && ('Y' == $o->getForcedValue())) {
			if ('Y' != Mage::helper('temando')->getConfigData('insurance/confirm_' . Mage::helper('temando')->getConfigData('insurance/status'))) {
			    $error = 'Please agree to the insurance terms & conditions at System -> Configuration -> Temando Settings -> Insurance.';
			}
		    }
		}

		if (!$error) {
		    // try to make booking
		    try {
			$booking_result = $this->_makeBooking($shipment, $quote);
		    } catch (Exception $ex) {
			$error = $ex->getMessage();
		    }
		}
	    }
	}

	if (!$error && $booking_result) {

	    if (!isset($booking_result->bookingNumber)) {
		$booking_result->bookingNumber = null;
	    }
	    if (!isset($booking_result->consignmentNumber)) {
		$booking_result->consignmentNumber = null;
	    }
	    if (!isset($booking_result->consignmentDocument)) {
		$booking_result->consignmentDocument = null;
	    }
	    if (!isset($booking_result->consignmentDocumentType)) {
		$booking_result->consignmentDocumentType = null;
	    }
	    if (!isset($booking_result->requestId)) {
		$booking_result->requestId = null;
	    }
	    if (!isset($booking_result->labelDocument)) {
		$booking_result->labelDocument = null;
	    }
	    if (!isset($booking_result->labelDocumentType)) {
		$booking_result->labelDocumentType = '';
	    }
	    if (isset($booking_result->anytime)) {
		$shipment->setReadyDate((string) $booking_result->anytime->readyDate);
		$shipment->setReadyTime((string) $booking_result->anytime->readyTime);
	    }

	    $shipment
		    ->setAdminSelectedQuoteId($quote_id)
		    ->setBookingRequestId($booking_result->requestId)
		    ->setBookingNumber($booking_result->bookingNumber)
		    ->setConsignmentNumber($booking_result->consignmentNumber)
		    ->setConsignmentDocument($booking_result->consignmentDocument)
		    ->setConsignmentDocumentType($booking_result->consignmentDocumentType)
		    ->setLabelDocument($booking_result->labelDocument)
		    ->setLabelDocumentType($booking_result->labelDocumentType)
		    ->setStatus(Ewave_Temando_Model_System_Config_Source_Shipment_Status::BOOKED)
		    ->setAnticipatedCost($shipment->getSelectedQuotePermutation()->getTotalPrice())
		    ->save();

	    $magento_shipment = Mage::getModel('sales/convert_order')
		    ->toShipment($shipment->getOrder());
	    /* @var $magento_shipment Mage_Sales_Model_Order_Shipment */

	    $totalQty = 0;
	    foreach ($shipment->getOrder()->getAllItems() as $item) {
		if ($item->getQtyToShip() && !$item->getIsVirtual()) {
		    $magento_shipment_item = Mage::getModel('sales/convert_order')
			    ->itemToShipmentItem($item);

		    $qty = $item->getQtyToShip();

		    $magento_shipment_item->setQty($qty);
		    $magento_shipment->addItem($magento_shipment_item);

		    $totalQty += $qty;
		}
	    }

	    $magento_shipment->setTotalQty($totalQty);

	    $track = Mage::getModel('sales/order_shipment_track');
	    /* @var Mage_Sales_Model_Order_Shipment_Track */
	    $number = '';
	    if ($booking_result->consignmentNumber) {
		$number .= 'Consignment Number: ' . $booking_result->consignmentNumber;
	    }

	    if ($booking_result->requestId) {
		if ($number) {
		    $number .= "\n<br />";
		}

		$number .= 'Request Id: ' . $booking_result->requestId;
	    }

	    $track
		    ->setCarrierCode(Mage::getModel('temando/shipping_carrier_temando')->getCarrierCode())
		    ->setTitle($quote->getCarrier()->getCompanyName())
		    ->setNumber($number);

	    $magento_shipment
		    ->addTrack($track)
		    ->register();

	    try {
		$magento_shipment->getOrder()->setIsInProcess(true)->setCustomerNoteNotify(true);
		Mage::getModel('core/resource_transaction')
			->addObject($shipment)
			->addObject($magento_shipment)
			->addObject($magento_shipment->getOrder())
			->save();
		$magento_shipment->sendEmail();
	    } catch (Mage_Core_Exception $e) {
		$error = $e->getMessage();
	    }

	    $this->_getSession()->addSuccess($this->__('Shipment booked.'));
	}

	if ($error) {
	    $this->_getSession()
		    ->addError($this->__($error))
	    /* ->setFormData($data) */;
	}
	$this->_redirect('*/*/edit', array('id' => $shipment_id));
    }

    public function massRemoveAction() {
	$params = $this->getRequest()->getParams();
	if (!isset($params['massaction']) || !is_array($params['massaction']) || empty($params['massaction'])) {
	    $this->_getSession()->addError(Mage::helper('temando')->__('No shipments selected for removal.'));
	    $this->_redirect('*/*/');
	}

	$shipment_ids = $params['massaction'];
	$notices = array();

	$count = 0;
	foreach ($shipment_ids as $id) {
	    $shipment = Mage::getModel('temando/shipment')->load($id);

	    if (!$shipment->getId()) {
		$notices[] = "Shipment ID $id not found.";
		continue;
	    }

	    $shipment->setGridDisplay('0');
	    $shipment->save();
	    $count++;
	}

	if (!empty($notices)) {
	    foreach ($notices as $notice)
		$this->_getSession()->addError($notice);
	}

	$this->_getSession()->addSuccess("Total of $count shipments removed.");

	//back to index shipment grid
	$this->_redirect('*/*/');

	return;
    }

    public function massBookAction() {
	$params = $this->getRequest()->getParams();
	if (!isset($params['massaction']) || !is_array($params['massaction']) || empty($params['massaction'])) {
	    $this->_getSession()->addError(Mage::helper('temando')->__('No shipments selected for booking.'));
	    $this->_redirect('*/*/');
	}

	$shipment_ids = $params['massaction'];
	$notices = array();

	foreach ($shipment_ids as $id) {
	    $shipment = Mage::getModel('temando/shipment')->load($id);

	    if (!$shipment->getId()) {
		$notices[] = "Shipment ID $id not found.";
		continue;
	    }

	    $order = Mage::getModel('sales/order')->load($shipment->getOrderId());
	    $shipping_quote = Mage::getModel('temando/quote')->load($shipment->getAdminSelectedQuoteId());

	    if (!$shipping_quote->getId()) {
		$notices[] = "Please select shipping quote for order # " . $order->getIncrementId();
		continue;
	    }

	    if ($shipment->getStatus() === Ewave_Temando_Model_System_Config_Source_Shipment_Status::BOOKED) {
		$notices[] = "Shipment for order # " . $order->getIncrementId() . " is already booked.";
		continue;
	    }

	    //all good - try to make booking
	    try {
		$booking_result = $this->_makeBooking($shipment, $shipping_quote);
		$this->_processBookingResult($booking_result, $shipment, $shipping_quote);
		$this->_getSession()->addSuccess('Shipment booked for order # ' . $order->getIncrementId());
	    } catch (Exception $ex) {
		$notices[] = "Shipment for order # " . $order->getIncrementId() . ': ' . $ex->getMessage();
	    }
	}

	if (!empty($notices)) {
	    foreach ($notices as $notice)
		$this->_getSession()->addError($notice);
	}

	//back to index shipment grid
	$this->_redirect('*/*/');

	return;
    }

    protected function _makeBooking(Ewave_Temando_Model_Shipment $shipment, Ewave_Temando_Model_Quote $quote) {
	$order = $shipment->getOrder();
	/* @var $order Mage_Sales_Model_Order */

	if (Mage::helper('temando')->isVersion2()) {
	    $request = Mage::getModel('temando/api_v2_request');
	    $origin = Mage::getModel('temando/warehouse')->load($shipment->getWarehouseId());

	    $origin->setState($origin->getRegion())
		    ->setPhone1($origin->getData('contact_phone_1'))
		    ->setPhone2($origin->getData('contact_phone_2'))
		    ->setEmail($origin->getContactEmail())
		    ->setFax($origin->getContactFax());

	    //Mage::log($origin->getData(), null, 'origin-data.log', true);
	} else {
	    $request = Mage::getModel('temando/api_request');
	    $origin = Mage::helper('temando')->getOrigin();
	}

	/* @var $request Ewave_Temando_Model_Api_Request */
	$request
		->setMagentoQuoteId($shipment->getOrder()->getQuoteId())
		->setOrigin(
			$origin->getCountry(), $origin->getPostcode(), $origin->getCity(), $origin->getType() ? $origin->getType() : $origin->getLocationType())
		->setDestination(
			$shipment->getDestinationCountry(), $shipment->getDestinationPostcode(), $shipment->getDestinationCity(), $shipment->getDestinationStreet())
		->setItems($shipment->getBoxes()->getItems());
	if ($shipment->getReadyDate()) {
	    $request->setReady(strtotime($shipment->getReadyDate()), $shipment->getReadyTime());
	} else {
	    $request->setReady(null);
	}

	$request_array = $request->toRequestArray();
	
	if (Mage::helper('temando')->isVersion2()) {
	    $originDesc = $origin->getName();
	} else {
	    $originDesc = 'Default Warehouse';
	}

	$request_array['origin'] = array(
	    'description' => $originDesc,
	    'contactName' => $origin->getContactName(),
	    'companyName' => $origin->getCompanyName(),
	    'street' => $origin->getStreet(),
	    'suburb' => $origin->getCity(),
	    'state' => $origin->getState(),
	    'code' => $origin->getPostcode(),
	    'country' => $origin->getCountry(),
	    'phone1' => $origin->getPhone1(),
	    'phone2' => $origin->getPhone2(),
	    'fax' => $origin->getFax(),
	    'email' => $origin->getEmail(),
	);

	$request_array['destination'] = array(
	    'contactName' => $shipment->getDestinationContactName(),
	    'companyName' => $shipment->getDestinationCompanyName(),
	    'street' => $shipment->getDestinationStreet(),
	    'suburb' => $shipment->getDestinationCity(),
	    'state' => $shipment->getDestinationRegion(),
	    'code' => sprintf("%04d", $shipment->getDestinationPostcode()),
	    'country' => $shipment->getDestinationCountry(),
	    'phone1' => $shipment->getDestinationPhone(),
	    'phone2' => '',
	    'fax' => '',
	    'email' => $shipment->getDestinationEmail(),
	);

	$option_array = $shipment->getOptionsArray();
	if (!is_null(Mage::getSingleton('adminhtml/session')->getData('insurance_' . $shipment->getId()))) {
	    $option_array['insurance'] = Mage::getSingleton('adminhtml/session')->getData('insurance_' . $shipment->getId());
	}

	$request_array['quote'] = $quote->toBookingRequestArray($option_array);

	$request_array['payment'] = array(
	    'paymentType' => Mage::helper('temando')->getConfigData('general/payment_type'),
	);

	if (Mage::helper('temando')->getConfigData('options/label_type')) {
	    $request_array['labelPrinterType'] = Mage::helper('temando')->getConfigData('options/label_type');
	}

	$request_array['reference'] = $order->getIncrementId();

	if (Mage::helper('temando')->isVersion2()) {
	    $api = Mage::getModel('temando/api_v2_client');
	} else {
	    $api = Mage::getModel('temando/api_client');
	}
	/* @var $api Ewave_Temando_Model_Api_Client */
	$api->connect(
		Mage::helper('temando')->getConfigData('general/username'), Mage::helper('temando')->getConfigData('general/password'), Mage::helper('temando')->getConfigData('general/sandbox'));
	return $api->makeBookingByRequest($request_array);
    }

    protected function _processBookingResult($booking_result, Ewave_Temando_Model_Shipment $shipment, Ewave_Temando_Model_Quote $quote) {
	if ($booking_result) {

	    if (!isset($booking_result->bookingNumber)) {
		$booking_result->bookingNumber = null;
	    }
	    if (!isset($booking_result->consignmentNumber)) {
		$booking_result->consignmentNumber = null;
	    }
	    if (!isset($booking_result->consignmentDocument)) {
		$booking_result->consignmentDocument = null;
	    }
	    if (!isset($booking_result->consignmentDocumentType)) {
		$booking_result->consignmentDocumentType = null;
	    }
	    if (!isset($booking_result->requestId)) {
		$booking_result->requestId = null;
	    }
	    if (!isset($booking_result->labelDocument)) {
		$booking_result->labelDocument = null;
	    }
	    if (!isset($booking_result->labelDocumentType)) {
		$booking_result->labelDocumentType = '';
	    }
	    if (isset($booking_result->anytime)) {
		$shipment->setReadyDate((string) $booking_result->anytime->readyDate);
		$shipment->setReadyTime((string) $booking_result->anytime->readyTime);
	    }

	    $shipment
		    ->setBookingRequestId($booking_result->requestId)
		    ->setBookingNumber($booking_result->bookingNumber)
		    ->setConsignmentNumber($booking_result->consignmentNumber)
		    ->setConsignmentDocument($booking_result->consignmentDocument)
		    ->setConsignmentDocumentType($booking_result->consignmentDocumentType)
		    ->setLabelDocument($booking_result->labelDocument)
		    ->setLabelDocumentType($booking_result->labelDocumentType)
		    ->setStatus(Ewave_Temando_Model_System_Config_Source_Shipment_Status::BOOKED)
		    ->setAnticipatedCost($shipment->getSelectedQuotePermutation()->getTotalPrice())
		    ->save();

	    $magento_shipment = Mage::getModel('sales/convert_order')
		    ->toShipment($shipment->getOrder());
	    /* @var $magento_shipment Mage_Sales_Model_Order_Shipment */

	    $totalQty = 0;
	    foreach ($shipment->getOrder()->getAllItems() as $item) {
		if ($item->getQtyToShip() && !$item->getIsVirtual()) {
		    $magento_shipment_item = Mage::getModel('sales/convert_order')
			    ->itemToShipmentItem($item);

		    $qty = $item->getQtyToShip();

		    $magento_shipment_item->setQty($qty);
		    $magento_shipment->addItem($magento_shipment_item);

		    $totalQty += $qty;
		}
	    }

	    $magento_shipment->setTotalQty($totalQty);

	    $track = Mage::getModel('sales/order_shipment_track');
	    /* @var Mage_Sales_Model_Order_Shipment_Track */
	    $number = '';
	    if ($booking_result->consignmentNumber) {
		$number .= 'Consignment Number: ' . $booking_result->consignmentNumber;
	    }

	    if ($booking_result->requestId) {
		if ($number) {
		    $number .= "\n<br />";
		}

		$number .= 'Request Id: ' . $booking_result->requestId;
	    }

	    $track
		    ->setCarrierCode(Mage::getModel('temando/shipping_carrier_temando')->getCarrierCode())
		    ->setTitle($quote->getCarrier()->getCompanyName())
		    ->setNumber($number);

	    $magento_shipment
		    ->addTrack($track)
		    ->register();

	    try {
		$magento_shipment->getOrder()->setIsInProcess(true)->setCustomerNoteNotify(true);
		Mage::getModel('core/resource_transaction')
			->addObject($shipment)
			->addObject($magento_shipment)
			->addObject($magento_shipment->getOrder())
			->save();
		$magento_shipment->sendEmail();
	    } catch (Mage_Core_Exception $e) {
		$error = $e->getMessage();
	    }
	}

	return;
    }

    public function consignmentAction() {
	$shipment = Mage::getModel('temando/shipment')->load($this->getRequest()->getParam('id'));
	/* @var $shipment Ewave_Temando_Model_Shipment */
	
	if ($shipment->getId()) {
	    $document = '';
	    $labelType = Mage::helper('temando')->getConfigData('options/label_type');
	    
	    switch($labelType) {
		case Ewave_Temando_Model_System_Config_Source_Labeltype::STANDARD:
		    if($shipment->getConsignmentDocument())
			$document = base64_decode ($shipment->getConsignmentDocument());
		    break;
		case Ewave_Temando_Model_System_Config_Source_Labeltype::THERMAL:
		    if($shipment->getLabelDocument())
			$document = base64_decode($shipment->getLabelDocument());
		    break;
	    }

	    $document_length = strlen($document);

	    if ($document_length) {
		$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader('Content-type', $shipment->getConsignmentDocumentType(), true)
			->setHeader('Content-Length', $document_length)
			->setHeader('Content-Disposition', 'attachment; filename="order-' . $shipment->getOrderNumber() . '.pdf"')
			->setHeader('Last-Modified', date('r'));
		$this->getResponse()->clearBody();
		$this->getResponse()->sendHeaders();
		print $document;
	    }
	}
    }

    public function cancelAction() {
	$shipment = Mage::getModel('temando/shipment')->load($this->getRequest()->getParam('id'));
	if (!$shipment->getId()) {
	    $this->_getSession()->addError('Invalid shipment id specified.');
	    $this->_redirect('*/*/'); // just redirect to grid if no shipment
	    return;
	}

	$requestId = $shipment->getBookingRequestId();
	$status = $shipment->getStatus();

	if ($requestId && $status == Ewave_Temando_Model_System_Config_Source_Shipment_Status::BOOKED) {
	    try {
		if (Mage::helper('temando')->isVersion2()) {
		    $api = Mage::getModel('temando/api_v2_client');
		} else {
		    $api = Mage::getModel('temando/api_client');
		}
		/* @var $api Ewave_Temando_Model_Api_Client */
		$api->connect(
			Mage::helper('temando')->getConfigData('general/username'), 
			Mage::helper('temando')->getConfigData('general/password'), 
			Mage::helper('temando')->getConfigData('general/sandbox'));
		
		$api->cancelRequest($requestId);
		//change temando shipment status back to pending
		$shipment->setStatus(Ewave_Temando_Model_System_Config_Source_Shipment_Status::PENDING);
		$shipment->save();
		
		//TODO: revert magento order shipment
		
		$this->_getSession()->addSuccess('Booking successfully cancelled.');
	    } catch (Exception $e) {
		$this->_getSession()->addError($e->getMessage());
	    }
	} else {
	    //shipment not booked - cannot cancel
	    $this->_getSession()->addError('Shipment does not have any booking associated with it. Unable to cancel.');
	}
	
	$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
	return;
    }

    public function labelAction() {
	$shipment = Mage::getModel('temando/shipment')
		->load($this->getRequest()->getParam('id'));
	/* @var $shipment Ewave_Temando_Model_Shipment */
	if ($shipment->getId()) {
	    $document = base64_decode($shipment->getLabelDocument());
	    $document_length = strlen($document);

	    if ($document_length) {
		$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader('Content-type', $shipment->getLabelDocumentType(), true)
			->setHeader('Content-Length', $document_length)
			->setHeader('Content-Disposition', 'attachment; filename="order-label-' . $shipment->getOrderNumber() . '.pdf"')
			->setHeader('Last-Modified', date('r'));
		$this->getResponse()->clearBody();
		$this->getResponse()->sendHeaders();
		print $document;
	    }
	}
    }

    public function selectAction() {
	$params = $this->getRequest()->getParams();
	if (isset($params['shipment']) && isset($params['quote'])) {
	    $shipment = Mage::getModel('temando/shipment')->load($params['shipment']);
	    /* @var $shipment Ewave_Temando_Model_Shipment */

	    $quote = Mage::getModel('temando/quote')->load($params['quote']);
	    /* @var $quote Ewave_Temando_Model_Quote */

	    foreach ($shipment->getOptions() as $_option) {
		/* @var $_option Ewave_Temando_Model_Option_Abstract */
		switch ($_option->getId()) {
		    case 'insurance':
			if ($_option->getForcedValue() === 'Y') {
			    $quote->setInsuranceIncluded(true);
			}
			break;
		    case 'carbonoffset':
			if ($_option->getForcedValue() === 'Y') {
			    $quote->setCarbonIncluded(true);
			}
			break;
		}
	    }

	    $shipment->setAdminSelectedQuoteId($quote->getId())
		    ->setAnticipatedCost($quote->getTotalPriceIncSelectedExtras())
		    ->save();

	    $this->_getSession()->addSuccess('Quote successfully selected for booking.');
	    $this->_redirect('*/*/edit', array('id' => $shipment->getId()));
	} else {
	    $this->_getSession()->addError('Shipment/Quote combination does not exist.');
	    $this->_redirect('*/*/');
	}
    }

}
