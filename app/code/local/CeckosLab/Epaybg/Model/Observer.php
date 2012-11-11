<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   CeckosLab
 * @package    CeckosLab_Epaybg
 * @copyright  Copyright (c) 2011 Tsvetan Stoychev
 * @copyright  Copyright (c) 2011  (http://www.ceckoslab.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class CeckosLab_Epaybg_Model_Observer {
	
	public function __construct() {

	}
	
	
	/*
	 * Los order information before customer to be redirected to epay.bg
	 */
	public function log_redirect_method($observer) {
		$event = $observer->getEvent();
		$order = $event->getOrder();
		
		$payment = Mage::getModel('epaybg/payment');
		
		$payment->setPaymentMethod($order->getPayment()->getMethod());
		$payment->setOrderIncrementId($order->getIncrementId());
		$payment->setCustomerId($order->getCustomerId());
		$payment->setCustomerEmail($order->getCustomerEmail());
		$payment->setStatus('PENDING');
		$payment->setCreatedAt(now());
		$payment->save();
		
		return $this;
	}
	
	/*
	 * Logs order information when we use method, taht doesn't redirect customer to epay.bg
	 * Usually we recive special number, that we should remember
	 */
	public function log_noredirect_method($observer) {
		$event = $observer->getEvent();
		$order = $event->getOrder();
		$idn = $event->getIdn();
		
		//Sending emial with IDN number to customer
		$emailHelper = Mage::helper('epaybg/email');
		$idnSent = $emailHelper->sendIdn($idn, $order);
		
		$payment = Mage::getModel('epaybg/payment');
		
		$payment->setPaymentMethod($order->getPayment()->getMethod());
		$payment->setOrderIncrementId($order->getIncrementId());
		$payment->setCustomerId($order->getCustomerId());
		$payment->setCustomerEmail($order->getCustomerEmail());
		$payment->setStatus('PENDING');
		$payment->setCreatedAt(now());
		
		if($error = $idn->getError()) {
			$payment->setHasError(1);
			$payment->setErrorMessage($error);
		}
		
		if($idnSent) {
			$payment->setIsIdnNotified(1);
		}
		
		if($number = $idn->getIdn()) {
			$payment->setIdn($number);
		}
		
		$payment->save();
		
		return $this;
	}
	
	public function log_api_error_noredirect_method($observer) {
		$event = $observer->getEvent();
		$order = $event->getOrder();
		$error = $event->getError();
		
		//Sending emial with IDN number to customer
		$emailHelper = Mage::helper('epaybg/email');
		//$idnSent = $emailHelper->sendIdn($idn, $order);
		
		$payment = Mage::getModel('epaybg/payment');
		
		$payment->setPaymentMethod($order->getPayment()->getMethod());
		$payment->setOrderIncrementId($order->getIncrementId());
		$payment->setCustomerId($order->getCustomerId());
		$payment->setCustomerEmail($order->getCustomerEmail());
		$payment->setStatus('ERROR');
		$payment->setCreatedAt(now());
		
		$payment->setHasError(1);
		$payment->setApiErrorMessage($error);
		
		$payment->save();
		
		$emailHelper = Mage::helper('epaybg/email');
		$idnSent = $emailHelper->sendApiError($error, $order);
		
		return $this;
	}
	
	public function log_connection_error_noredirect_method($observer) {
		$event = $observer->getEvent();
		$order = $event->getOrder();
		$error = $event->getError();
		
		//Sending emial with IDN number to customer
		$emailHelper = Mage::helper('epaybg/email');
		//$idnSent = $emailHelper->sendIdn($idn, $order);
		
		$payment = Mage::getModel('epaybg/payment');
		
		$payment->setPaymentMethod($order->getPayment()->getMethod());
		$payment->setData('payment_method',$order->getPayment()->getMethod());

		$payment->setOrderIncrementId($order->getIncrementId());
		$payment->setCustomerId($order->getCustomerId());
		$payment->setCustomerEmail($order->getCustomerEmail());
		$payment->setStatus('ERROR');
		$payment->setCreatedAt(now());
		
		$payment->setHasError(1);
		$payment->setConnectionErrorMessage($error);
		
		$payment->save();
		
		$emailHelper = Mage::helper('epaybg/email');
		$idnSent = $emailHelper->sendConnectionError($error, $order);
		
		return $this;
	}
	
	/*
	 * Logs response information from epaybg
	 * 
	 */
	public function log_epaybg_invoice($observer) {
		$event = $observer->getEvent();
		$invoice = $event->getInvoice();

		$payment = Mage::getModel('epaybg/payment')->load($invoice['invoice'], 'order_increment_id');
		
		$payment->setStatus($invoice['status']);
		$payment->setStan($invoice['stan']);
		$payment->setBcode($invoice['bcode']);
		$payment->setUpdatedAt(now());
		
		$payment->save();	
		
		return $this;
	}
	
}