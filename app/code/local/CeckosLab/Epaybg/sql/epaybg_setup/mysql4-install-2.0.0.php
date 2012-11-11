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
$pathsReplace = array(
	'payment/epaybg/trader_number' => 'epaybg/settings/trader_number',
	'payment/epaybg/exparation_time' => 'epaybg/settings/exparation_time',
	'payment/epaybg/secret_key' => 'epaybg/settings/secret_key',
	'payment/epaybg/trader_email' => 'epaybg/settings/trader_email',
	'payment/epaybg/sandbox_flag' => 'epaybg/settings/sandbox_flag'
);

$pathsDelete = array(
	'payment/epaybg/order_status',
	'payment/epaybg/order_status_after_payment'
);

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('epaybg/payment')}`;
CREATE TABLE `{$this->getTable('epaybg/payment')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_increment_id` varchar(50) DEFAULT NULL,
  `payment_method` varchar(128) NOT NULL,
  `idn` varchar(10) NOT NULL,
  `status` varchar(128) NOT NULL,
  `stan` varchar(64) NOT NULL,
  `bcode` varchar(64) NOT NULL,
  `has_error` int(1) NOT NULL DEFAULT '0',
  `api_error_message` text NOT NULL,
  `connection_error_message` text NOT NULL,
  `customer_id` int(10) DEFAULT NULL,
  `customer_email` varchar(255) NOT NULL,
  `is_idn_notified` int(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='ePay Bg payments' ;

    ");

$conn = $installer->getConnection();

/* Replacing some config paths, that we need to work with new confing interface */
foreach ($pathsReplace as $searchPath => $replacePath) {
	$select = $conn
	    ->select()
	    ->from($this->getTable('core/config_data'), array('scope', 'scope_id', 'path', 'value'))
	    ->where(new Zend_Db_Expr("path LIKE '{$searchPath}'"));
	$data = $conn->fetchAll($select);
	
	if (!empty($data)) {
	    foreach ($data as $key => $value) {
	        $data[$key]['path'] = $replacePath;
	        break;
	    }
	    $conn->insertOnDuplicate($this->getTable('core/config_data'), $data, array('path'));
	    $conn->delete($this->getTable('core/config_data'), new Zend_Db_Expr("path LIKE '{$searchPath}'"));
	}
}

/* Deletig some old rows from core_config_data, that we don't need */
foreach ($pathsDelete as $path) {
    $conn->delete($this->getTable('core/config_data'), new Zend_Db_Expr("path LIKE '{$path}'"));
}

$installer->endSetup();
