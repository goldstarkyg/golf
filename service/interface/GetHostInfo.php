<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';

class GetHostInfo extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }
    
    public function verifyInput(&$args)
    {
        //$req = $args['interface']['para'];
    
        $rules = array(
            'userid' => array('type' => 'string'),
            'password' => array('type' => 'string')
        );
    
        return $this->_verifyInput($args, $rules);
    }
    
    public function process()
    {
        interface_log(INFO, EC_OK,"GetHostInfo args=" . var_export($this->_args, true));
    	
        
        $config = getConf('ROUTE.DB');
        
        
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $error_message = "";       
       
        $userid = $this->_args['userid'];
        $password = $this->_args['password'];

        $result = '';
 		$ret = $dao_live->getHostInfo($userid, $password, $result);


    	if($ret != 0)
    	{
    		$this->_retValue =$ret;
    		$this->_retMsg = 'GetHostInfo::process() fail '.genErrMsg($this->_retValue);
    		return false;
    	} 	
          
        $this->_retValue = EC_OK;
        $this->_data= $result;
        interface_log(INFO, EC_OK, 'GetHostInfo::process() succeed');
        return true;
    }
}

?>
