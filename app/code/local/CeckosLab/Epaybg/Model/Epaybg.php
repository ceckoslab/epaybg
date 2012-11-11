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

class CeckosLab_Epaybg_Model_Epaybg extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'epaybg';
    protected $_formBlockType = 'epaybg/form';
    protected $_infoBlockType = 'epaybg/info';
    
    protected $defaultExpDays = 20;
    
	protected $_paymentReqestUrl = array(
    	'production' => 'https://www.epay.bg/',
		'sandbox' => 'https://devep2.datamax.bg/ep2/epay2_demo/'
	);

    /**
     * Availability options
     */
    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    
    //Epay Bg allowed currencies
    protected $_allowCurrencyCode = array(
    	'BGN','EUR','USD'
    );

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
    
    //Validate currencies
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (isset($currency_code)) {
	        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
	            Mage::throwException(__('Please slect currency between United States dollar, Euro and Bulgarian lev. Selected currency code is not compatabile with Epay Bg.'));
	        }
	      }
        return $this;
    }
    
    //Redirect to the page where the form for Epay Bg is generated
    public function getOrderPlaceRedirectUrl()
    {
    		return Mage::getUrl('epaybg/standard/redirect');
    }
    
    //Return the url where the form with payment information will be sumbited
    public function getEpaybgUrl($flag = true)
    {
         if ($flag) {
             $url = $this->_paymentReqestUrl['sandbox'];;
         } else {
             $url = $this->_paymentReqestUrl['production'];
         }
         return $url;
    }
    
    public function getDefaultExpDays() {
    	return $this->defaultExpDays;
    }
    
}