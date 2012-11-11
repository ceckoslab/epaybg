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

class CeckosLab_Epaybg_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_TRADER_EMAIL		= 'epaybg/settings/trader_email';
    const XML_PATH_TRADER_NUMBER  	= 'epaybg/settings/trader_number';
    const XML_PATH_SECRET_KEY   	= 'epaybg/settings/secret_key';
    const XML_PATH_SANDBOX_FLAG   	= 'epaybg/settings/sandbox_flag';
    const XML_PATH_EXPARATION_TIME 	= 'epaybg/settings/exparation_time';
    
	
	protected $epaybg = null;
	protected $epayLib = null;
	protected $order = null;
	protected $encoded = null;
	protected $storeId = null;

	public function __construct() {

	}
	
	public function init() {
		//Getting the Epaybg model
		$this->epaybg = Mage::getModel('epaybg/epaybg');
		
        if (!$this->epaybg->getCheckout()->getLastSuccessQuoteId()) {
            return false;
        }

        $lastQuoteId = $this->epaybg->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->epaybg->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            return false;
        }
        
       	$order = Mage::getModel('sales/order')->loadByIncrementId($this->epaybg->getCheckout()->getLastRealOrderId());
		
		$this->order = $order;
				
		$this->epayLib = Mage::getModel('epaybg/epaylib');
		$this->storeId = Mage::app()->getStore()->getId();
		$this->encoded = $this->generateEncoded();
		
		return $order;
	}
	
	private function generateEncoded() {
		//format exp date string
		$expDays = $this->getExpDays();
		$expDays = intval($expDays);
	    	
		$expDaysFormated = '';
	    	
		if( $expDays != 0 ) {
			$expDaysFormated = date('d.m.Y h:m:s', strtotime("+{$expDays} days"));
		} else {
			$expDaysFormated = date('d.m.Y h:m:s', strtotime("+{$this->epaybg->getDefaultExpDays()} days"));
		}
		
		$data = '';
	    	
		if($this->getTraderNumber()) {
			$data .= "MIN={$this->getTraderNumber()}" . "\n";
		} elseif($this->getTraderEmail()) {
			$data .= "EMAIL={$this->getTraderEmail()}" . "\n";
		}
		
		$data .= "INVOICE={$this->getOrderId()}" . "\n";
		$data .= "AMOUNT=" . number_format($this->getOrderGrantTotal(), 2, '.', '') . "\n";
		if($this->getPaymentMethod() != 'epaybg_easypaybpay') {
			$data .= "CURRENCY=" . $this->order->getOrderCurrency()->getCurrencyCode() . "\n";
		}
		$data .= "EXP_TIME=" . $expDaysFormated . "\n";
		$data .= "DESCR=Payment request from - " . $this->getStoreName()  . "\n";
		$data .= "ENCODING=utf-8\n";
			
		return base64_encode($data);
	}
	
	public function getEncoded() {
		return $this->encoded;
	}
	
	public function getIdnData($idn = null) {
		$data = explode('=', $idn);
		$varienObject = new Varien_Object();
		
		$varienObject->addData(
			array('has_error' => false)
		);
		
		if(!empty($data)) {
			if($data[0] == 'ERR') {
				$varienObject->addData(
					array(
						'error' => $this->win_to_utf8($data[1]),
						'has_error' => true
					)
				);
			} elseif($data[0] == 'IDN') {
				$varienObject->addData(
					array('idn' => $data[1])
				);
			} else {
				$varienObject->addData(
					array(
						'error' => 'Wrong idn string: ' . $idn,
						'has_error' => true
					)
					
				);
			}
		} else {
			$varienObject->addData(
				array(
					'error' => 'Wrong idn string: ' . $idn,
					'has_error' => true
				)
			);
		}
		
		return $varienObject;
	}
	
	public function convertDateTimestamp($date) {
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2); 
		$day = substr($date, 6, 2);
		$hour = substr($date, 8, 2);
		$minute = substr($date, 10, 2);
		$seconds = substr($date, 12, 2);
		
		return $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $seconds;
	}
	
	public function sendOrderPlacedEmail() {
			$this->order->sendNewOrderEmail();
			$this->order->setEmailSent(true);
			$this->order->save();
	}
	
	public function getChecksum() {
		return $this->epayLib->hmac($this->encoded, $this->getTraderSecretKey());
	}
	
	protected function getOrderId() {
		return $this->order->getData('increment_id');
	}
	
	protected function getOrderGrantTotal() {
		return $this->order->getData('grand_total');
	}
	
	public function getStoreName() {
		$store = Mage::getModel('core/store')->load($this->storeId);
		$storeName = $store->getFrontendName();
		
		return $storeName;
	}
	
	public function getPaymentMethod() {
		return $this->order->getPayment()->getMethod();
	}
	
	public function getTraderEmail() {
		return Mage::getStoreConfig(self::XML_PATH_TRADER_EMAIL);
	}
	
	public function getTraderNumber() {
		return Mage::getStoreConfig(self::XML_PATH_TRADER_NUMBER);
	}
	
	public function getTraderSecretKey() {
		return Mage::getStoreConfig(self::XML_PATH_SECRET_KEY);
	}
	
	public function getSandBoxFlag() {
		return Mage::getStoreConfig(self::XML_PATH_SANDBOX_FLAG);
	}
	
	public function getExpDays() {
		return Mage::getStoreConfig(self::XML_PATH_EXPARATION_TIME);
	}
	
	public function getEpaybgRedirectUrl() {
		return Mage::getModel('epaybg/epaybg')->getEpaybgUrl($this->getSandBoxFlag());
	}
	
	public function getEpaybgSilentReqestUrl() {
		return Mage::getModel('epaybg/easypaybpay')->getEpaybgUrl($this->getSandBoxFlag());
	}
	
	public function win_to_utf8 ($string)  {
	    $in_arr = array (
	        chr(208), chr(192), chr(193), chr(194),
	        chr(195), chr(196), chr(197), chr(168),
	        chr(198), chr(199), chr(200), chr(201),
	        chr(202), chr(203), chr(204), chr(205),
	        chr(206), chr(207), chr(209), chr(210),
	        chr(211), chr(212), chr(213), chr(214),
	        chr(215), chr(216), chr(217), chr(218),
	        chr(219), chr(220), chr(221), chr(222),
	        chr(223), chr(224), chr(225), chr(226),
	        chr(227), chr(228), chr(229), chr(184),
	        chr(230), chr(231), chr(232), chr(233),
	        chr(234), chr(235), chr(236), chr(237),
	        chr(238), chr(239), chr(240), chr(241),
	        chr(242), chr(243), chr(244), chr(245),
	        chr(246), chr(247), chr(248), chr(249),
	        chr(250), chr(251), chr(252), chr(253),
	        chr(254), chr(255)
	    );  
	        $out_arr = array (
	        chr(208).chr(160), chr(208).chr(144), chr(208).chr(145),
	        chr(208).chr(146), chr(208).chr(147), chr(208).chr(148),
	        chr(208).chr(149), chr(208).chr(129), chr(208).chr(150),
	        chr(208).chr(151), chr(208).chr(152), chr(208).chr(153),
	        chr(208).chr(154), chr(208).chr(155), chr(208).chr(156),
	        chr(208).chr(157), chr(208).chr(158), chr(208).chr(159),
	        chr(208).chr(161), chr(208).chr(162), chr(208).chr(163),
	        chr(208).chr(164), chr(208).chr(165), chr(208).chr(166),
	        chr(208).chr(167), chr(208).chr(168), chr(208).chr(169),
	        chr(208).chr(170), chr(208).chr(171), chr(208).chr(172),
	        chr(208).chr(173), chr(208).chr(174), chr(208).chr(175),
	        chr(208).chr(176), chr(208).chr(177), chr(208).chr(178),
	        chr(208).chr(179), chr(208).chr(180), chr(208).chr(181),
	        chr(209).chr(145), chr(208).chr(182), chr(208).chr(183),
	        chr(208).chr(184), chr(208).chr(185), chr(208).chr(186),
	        chr(208).chr(187), chr(208).chr(188), chr(208).chr(189),
	        chr(208).chr(190), chr(208).chr(191), chr(209).chr(128),
	        chr(209).chr(129), chr(209).chr(130), chr(209).chr(131),
	        chr(209).chr(132), chr(209).chr(133), chr(209).chr(134),
	        chr(209).chr(135), chr(209).chr(136), chr(209).chr(137),
	        chr(209).chr(138), chr(209).chr(139), chr(209).chr(140),
	        chr(209).chr(141), chr(209).chr(142), chr(209).chr(143)
	    );  
	        
	    $out = str_replace($in_arr,$out_arr,$string);

	    return $out;
	}
}
