<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../dao/dao_live/dao_live.class.php';

class VODFetchList extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }
    
    public function verifyInput(&$args)
    {
     //   $req = $args['interface']['para'];
    
        $rules = array(
			'user_id' => array('type' => 'string'),
        	'pageno'=>array('type' => 'int', 'range' => '[0,+)'),
        	'pagesize' => array('type' => 'int', 'range' => '[0,+)')
        );
    
        return $this->_verifyInput($args, $rules);
    }
    
    public function process()
    {
        interface_log(INFO, EC_OK,"VODFetchList args=" . var_export($this->_args, true));
        
        $page_no = 1;
        $page_size = 10;
        $all_status =0;
        if(array_key_exists('pageno', $this->_args) && (int)$this->_args['pageno'] > 0)
        {
        	$page_no = (int)$this->_args['pageno'];
        }
        if(array_key_exists('pagesize', $this->_args) && (int)$this->_args['pagesize'] >= 10 && (int)$this->_args['pagesize'] <= 100)
        {
        	$page_size = (int)$this->_args['pagesize'];
        }      
        
        $config = getConf('ROUTE.DB');
        $dao_live = new dao_live($config['HOST'], $config['PORT'], $config['USER'], $config['PASSWD'], $config['DBNAME']);
        $error_message = "";  
        $ret = 0;
        
        $all_count =0;
        $result_list = array();
        $start_pos = ($page_no -1) * ($page_size);

		$user_id = '';
		if(array_key_exists('user_id', $this->_args))
		{
			$user_id = $this->_args['user_id'];
		}
		$ret = $dao_live->getVODCount($all_count, $error_message, $user_id);
		if($ret == 0)
		{
			$ret = $dao_live->getVODList( $start_pos, $page_size,$result_list, $error_message, $user_id);
		}
    	
    	if($ret != 0)
    	{
    		$this->_retValue =$ret;
    		$error_message="db error:no permission";
    		$this->_retMsg = 'VODFetchList::process() fail '.genErrMsg($this->_retValue);
    		return false;
    	}

    	$this->_retValue = EC_OK;
    	$this->_data = array('totalcount' => $all_count,'pusherlist' => $result_list);
        interface_log(INFO, EC_OK, 'VODFetchList::process() succeed');
        return true;
    }
}

?>
