<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';
require_once dirname(__FILE__) . '/../common/GlobalDefine.php';
require_once dirname(__FILE__) . '/../conf/OutDefine.php';


class GetCOSSignV2 extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }
    
    public function verifyInput(&$args)
    {
        return true;
    }
    
    public function process()
    {
        interface_log(INFO, EC_OK,"GetCOSSignV2 args=" . var_export($this->_args, true)); 
   
        $currentTime = time();
        $expiredTime = $currentTime + COSKEY_EXPIRED_TIME;
	$keyTime = $currentTime . ';' . $expiredTime;
	$signStr = bin2hex(hash_hmac('SHA1', $keyTime, COSKEY_SECRECTKEY, true));
  		
        $this->_retValue = EC_OK;
        $this->_data=array("signKey"=>$signStr, "keyTime"=>$keyTime);
        interface_log(INFO, EC_OK, 'GetCOSSign::process() succeed.' .  $keyTime . '::' . $signStr);        
        return true;
    }
    
}

?>
