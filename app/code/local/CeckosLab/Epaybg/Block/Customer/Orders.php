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

class CeckosLab_Epaybg_Block_Customer_Orders extends Mage_Core_Block_Template
{
	public function __construct() 
	{
		parent::__construct();
		$customerId = Mage::getSingleton('customer/session')->getCustomerId();
		
		$collection = Mage::getModel('epaybg/payment')->getCollection()
			->addFieldToFilter('customer_id', array('eq' => $customerId))
			->addFieldToFilter('payment_method', array('eq' => 'epaybg_easypaybpay'))
			->addFieldToSelect('*')
			->setOrder('id', 'DESC');
			
		$this->setOrders($collection);
	}
}