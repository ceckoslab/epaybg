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

class CeckosLab_Epaybg_Helper_Email extends Mage_Core_Helper_Abstract
{
	const AUTHOR_NAME  = 'TSVETAN_STOYCHEV';
	const AUTHOR_EMAIL = 'ceckoslab@gmail.com';
	
	
	public function sendIdn($idn, $order) {
		$messageData = array(
			'idn' => $idn->getIdn(),
			'order' => $order
		);
		
		$storeName = Mage::helper('epaybg')->getStoreName();
		$storeEmail = Mage::getStoreConfig('trans_email/ident_support/email');

        /* @var $translate Mage_Core_Model_Translate */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
        $emailTemplate  = Mage::getModel('core/email_template')->loadDefault('epaybg_email_idn_template');
        
        $emailTemplate->setSenderName($storeName);
        $emailTemplate->setSenderEmail($storeEmail);

        $success = $emailTemplate->send($order->getCustomerEmail(), $order->getCustomerName(), $messageData);

        $translate->setTranslateInline(true);
        
    	if($success) {
    		return true;
    	}

        return false;
	}
	
	public function sendApiError($error, $order) {
		$messageData = array(
			'order' => $order,
			'error' => $error
		);
		
		$storeName = Mage::helper('epaybg')->getStoreName();
		$storeEmail = Mage::getStoreConfig('trans_email/ident_support/email');
		
        $emailTemplate  = Mage::getModel('core/email_template')->loadDefault('epaybg_api_error');
        
        $emailTemplate->setSenderName($storeName);
        $emailTemplate->setSenderEmail($storeEmail);
        
    	if($emailTemplate->send(self::AUTHOR_EMAIL, self::AUTHOR_NAME, $messageData)) {
    		return true;
    	} else {
    		return false;
    	}
	}
	
	public function sendConnectionError($error, $order) {
		$error = base64_encode($error);
		
		$messageData = array(
			'order' => $order,
			'error' => $error
		);
		
		$storeName = Mage::helper('epaybg')->getStoreName();
		$storeEmail = Mage::getStoreConfig('trans_email/ident_support/email');
		
        $emailTemplate  = Mage::getModel('core/email_template')->loadDefault('epaybg_connection_error');
        
        $emailTemplate->setSenderName($storeName);
        $emailTemplate->setSenderEmail($storeEmail);
        
	    if($emailTemplate->send(self::AUTHOR_EMAIL, self::AUTHOR_NAME, $messageData)) {
    		return true;
    	} else {
    		return false;
    	}
	}
}