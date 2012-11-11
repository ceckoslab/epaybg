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

class CeckosLab_Epaybg_StandardController extends Mage_Core_Controller_Front_Action
{
	public function redirectAction() { 
		$helper = Mage::helper('epaybg');
       	$order = $helper->init();
       	
       	if($order instanceof Mage_Sales_Model_Order) {
			Mage::dispatchEvent('before_epaybg_redirect', 
				array(
					'order' => $order
				)
			);
			
			Mage::register('epaybg_paymentMethod', $helper->getPaymentMethod());
			Mage::register('epaybg_encoded', $helper->getEncoded());
			Mage::register('epaybg_checksum', $helper->getChecksum());
			
			//Sending email notification for placed order
			$helper->sendOrderPlacedEmail();
	        
			// Load layout
			// We generate html form, that is used to submit the payment data to Epay Bg
			$this->getResponse()->setBody($this->getLayout()->createBlock('epaybg/standard_redirect')->toHtml());
       	} else {
       		$this->_redirect('checkout/cart');
       	}
	}
	
	//Action for Epay Bg methods EasyPay and B-Pay
	public function noredirectAction() {
		$helper = Mage::helper('epaybg');
       	$order = $helper->init();

       	if($order instanceof Mage_Sales_Model_Order) {
       		$session = Mage::getSingleton('checkout/session');
       		
       	
			//Sending email notification for placed order
			$helper->sendOrderPlacedEmail();
	       	
			$encoded = $helper->getEncoded();
			$checksum = $helper->getChecksum();
			
			/*
			 * Returns 2 possible values. If sanbox flag is set, return url for tests evirontmet
			 * If not returns url for production environmet
			 */
			$url = $helper->getEpaybgSilentReqestUrl();
			
	        $client = new Varien_Http_Client($url);
	        $client->setMethod(Zend_Http_Client::GET);
	        $client->setParameterGet('ENCODED', $encoded);
	        $client->setParameterGet('CHECKSUM', $checksum);
	        
			try {
	            $response = $client->request();
	        } catch (Exception $e) {
	            $exception = 'HTTP Request failed: ' . $e->getMessage();
	            
				Mage::dispatchEvent('epaybg_connection_error', 
					array(
						'order' => $order,
						'error' => $exception
					)
				);
				
				Mage::register('errorMessage', $exception);
	            
	            $this->loadLayout();
				$this->renderLayout();
			
	            return false;
	        }
	        
	        $status = $response->getStatus();
	        $body = $response->getRawBody();
	        if ($status == 200 || ($status == 400 && !empty($body))) {
	        	$idn = $helper->getIdnData($body);
	        	
	        	if($hasError = $idn->getHasError()) {
	        		Mage::dispatchEvent('epaybg_idn_api_error', 
						array(
							'order' => $order,
							'error' => $idn->getError()
						)
					);
					Mage::register('errorMessage', $idn->getError());
	        	} else {
					Mage::dispatchEvent('after_epaybg_idn_received', 
						array(
							'order' => $order,
							'idn' => $idn
						)
					);
					
					//Adding comment with 10-digits number to the order 
					$order->addStatusHistoryComment('EasyPay and B-Pay 10-digits number: ' . $idn->getIdn());
					$order->save();
					
					Mage::register('order', $order);
					Mage::register('idnObject', $idn);
	        	}
	        } else {
	        	Mage::dispatchEvent('epaybg_connection_error', 
					array(
						'order' => $order,
						'error' => $body
					)
				);
				
				Mage::register('errorMessage', 'Bad HTTP response');
	        }
	        
	        $this->loadLayout();
			$this->renderLayout();
			
			$session->clear();
       	} else {
       		$this->_redirect('checkout/cart');
       	}
	}
	
	//This action is triggered when Epay Bg send PING wich contain order/s status
	public function responseAction() {
		
		//Getting the notification from Epay Bg
		$encoded = $this->getRequest()->getPost('encoded');
		$checksum = $this->getRequest()->getPost('checksum');
		
		//Getting trader secret key
		$helper = Mage::helper('epaybg');
		$secret = $helper->getTraderSecretKey();
		
		//We need to provide response in plain text to Epay Bg PING
		$viewData = '';

		$epayLib = Mage::getModel('epaybg/epaylib');
		$epayLib->handleResponse($encoded, $checksum, $secret);
		
		if( $epayLib->isValid() ) {
			
			$invoices = $epayLib->getInvoices();
			
			if($invoices) {
				foreach ($invoices as $invoice) {
					
					$order = Mage::getModel('sales/order')->loadByIncrementId($invoice['invoice']);
					
					if($order->getId()) {
						switch ($invoice['status']) {
							case 'PAID' :
								{	
									$payTime = date("Y M d H:i", strtotime($helper->convertDateTimestamp($invoice['pay_date'])));
									
									$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 
									Mage_Sales_Model_Order::STATE_PROCESSING, 
									"Epay BG payment - RECIEVED\n
									PAY TIME - {$payTime}\n
									STAN - {$invoice['stan']}\n
									BCODE - {$invoice['bcode']}\n",
									$notified = true);
									
									$order->save();
									break;
								}
							case 'DENIED' : 
								{	
									$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, 
									'canceled', 
									'Epay BG payment - DENIED', 
									$notified = true);
									
									$order->save();
									break;
								}
							case 'EXPIRED' : 
								{	
									$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, 
									'canceled', 
									'Epay BG payment - EXPIRED', 
									$notified = true);
									
									$order->save();
									break;
								}
						}
						
						$viewData .= "INVOICE={$invoice['invoice']}:STATUS=OK\n";
						
						Mage::dispatchEvent('after_epaybg_payment_received', 
							array(
								'invoice' => $invoice
							)
						);
						
					} else {
						$viewData .= "INVOICE={$invoice['invoice']}:STATUS=NO\n";
					}
				}
			}
		
		} else {
			//What to do? We can send notification to admin, that there is something wrong!
			$viewData .= $epayLib->getError();
		}
		
		//Print the plane text for Epay Bg
		$this->getResponse()->setBody($viewData);
	}
	
    public function canceledAction() {
    	Mage::getSingleton('checkout/session')->clear();

		$this->loadLayout();
		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('epaybg/standard_canceled'));
		$this->renderLayout();
    }
}