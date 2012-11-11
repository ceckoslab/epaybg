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

class CeckosLab_Epaybg_Model_Easypaybpay extends CeckosLab_Epaybg_Model_Epaybg
{
    protected $_code  = 'epaybg_easypaybpay';
    protected $_formBlockType = 'epaybg/easypaybpay_form';
    protected $_infoBlockType = 'epaybg/easypaybpay_info';
    
    protected $_paymentReqestUrl = array(
    	'production' => 'https://www.epay.bg/ezp/reg_bill.cgi',
		'sandbox' => 'https://devep2.datamax.bg/ep2/epay2_demo/ezp/reg_bill.cgi'
	);
    
    //EasyPay and B-Pay allowed currnecies
    protected $_allowCurrencyCode = array(
    	'BGN'
    );
    
    //Validate currencies
    public function validate()
    {
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (isset($currency_code)) {
	        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
	            Mage::throwException(__('This payment method is available only in Bulgarian Levs.'));
	        }
		}
		parent::validate();	
        return $this;
    }
    
    //Redirect to the page where the form for Epay Bg is generated
    public function getOrderPlaceRedirectUrl()
    {
		return Mage::getUrl('epaybg/standard/noredirect');
    }
    
}