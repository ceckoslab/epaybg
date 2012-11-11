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

class CeckosLab_Epaybg_Block_Standard_Redirect extends Mage_Core_Block_Template
{
    protected function _construct()
    {
    	parent::_construct();
    }
    
    protected function _toHtml() {
    	$urlOk = Mage::getUrl('checkout/onepage/success');
    	$urlCancel = Mage::getUrl('epaybg/standard/canceled');
    	
		$encoded = Mage::registry('epaybg_encoded');
		$checksum = Mage::registry('epaybg_checksum');
		$paymentMethod = Mage::registry('epaybg_paymentMethod');
		
		$checkoutType = 'paylogin';
		
		switch ($paymentMethod) {
			case 'epaybg' 		: $checkoutType = 'paylogin'; break;
			case 'epaybg_cc'	: $checkoutType = 'credit_paydirect'; break;
		}
		
		$result = array();
        $result[] = "<input type=hidden name=PAGE value='{$checkoutType}' />";
        $result[] = "<input type=hidden name=ENCODED value='{$encoded}' />";
        $result[] = "<input type=hidden name=CHECKSUM value='{$checksum}' />";
        $result[] = "<input type=hidden name=URL_OK value='{$urlOk}' />";
        $result[] = "<input type=hidden name=URL_CANCEL value='{$urlCancel}' />";
        
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to ePay Bg in a few seconds.');
        $html.= '<form action="' . $this->getPostUrl() . '" method="post" name="epay_form" id="epay_form">';
		foreach ($result as $key => $value) {
			$html .= $value;
		}
		$html.= '<input type="submit" style="display:none;">';
		$html.= '</form>';
        
        $html.= '<script type="text/javascript">document.getElementById("epay_form").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
    
    protected function getPostUrl() {
    	return Mage::helper('epaybg/data')->getEpaybgRedirectUrl();
    }
}
