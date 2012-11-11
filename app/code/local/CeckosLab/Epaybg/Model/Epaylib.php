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

class CeckosLab_Epaybg_Model_Epaylib {
	
	protected $valid = true;
	protected $error = '';
	protected $invoices = null;
	
	
	public function hmac($data, $passwd, $algo = 'sha1'){
       
        $algo = strtolower($algo);
        $p = array('md5'=>'H32','sha1'=>'H40');
       
        if(strlen($passwd)>64) $passwd=pack($p[$algo],$algo($passwd));
        if(strlen($passwd)<64) $passwd=str_pad($passwd,64,chr(0));
   
        $ipad=substr($passwd,0,64) ^ str_repeat(chr(0x36),64);
        $opad=substr($passwd,0,64) ^ str_repeat(chr(0x5C),64);
   
        return($algo($opad.pack($p[$algo],$algo($ipad.$data))));
    }
    
    public function handleResponse($encoded, $checksum, $secret) {
        if (empty($encoded) || empty($checksum) || empty($secret)) {
            $this->valid = false;
        	$this->error = "ERR=Invalid response\n";
        	return ;
        }
       
	    $data = base64_decode($encoded);
	    
        $hmac = $this->hmac($encoded, $secret);
        
        if ($hmac == $checksum) {
           
            $lines_arr = split("\n", $data);
           
            $parsedData = array();
            $i = 0;
            foreach ($lines_arr as $line) {
                if (preg_match("/^INVOICE=(\d+):STATUS=(PAID|DENIED|EXPIRED)(:PAY_TIME=(\d+):STAN=(\d+):BCODE=([0-9a-zA-Z]+))?$/", $line, $regs)) {
   
					$parsedData[$i]['invoice'] = $regs[1];
					$parsedData[$i]['status'] = $regs[2];
					
					if(isset($regs[4])) {
						$parsedData[$i]['pay_date']	= $regs[4];
					}
					
					if(isset($regs[5])) {
						$parsedData[$i]['stan'] = $regs[5];
					}
					
					if(isset($regs[6])) {
						$parsedData[$i]['bcode'] = $regs[6];
					}
					
					$i++;
                }
            }
       
            $this->invoices =  $parsedData;
        } else {
        	$this->valid = false;
        	$this->error = "ERR=Not valid CHECKSUM\n";
        }
    }
    
    public function extractIdn($rawBody = null) {
    	if(!empty($rawBody)) {
			return explode("=", $rawBody);		
    	} else {
			return false;    		
    	}
    }
    
    public function isValid() {
    	return $this->valid;
    }
    
    public function getError() {
    	return $this->error;
    }
    
    public function getInvoices() {
    	return $this->invoices;
    }
}