<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../conf/OutDefine.php';
class MergeVideoStream extends AbstractInterface
{
    public function initialize()
    {
        return true;
    }

    public function verifyInput(&$args)
    {
        $req = $args['interface']['para'];

        $rules = array(
            'userid' => array('type' => 'string'),
            'mergeparams' => array('type' => 'array'),
        );

        return $this->_verifyInput($args, $rules);
    }

    public function process()
    {
        interface_log(INFO, EC_OK,"MergeVideoStream args=" . var_export($this->_args, true));

        $userid = $this->_args['userid'];
        $para = json_encode($this->_args['mergeparams']);
        $config = getConf('ROUTE.LIVE_WEBSERVICE');
        $url = $config['URL'];
        $time_now = time() + 60;
      	$md5_sign = GetCallBackSign($time_now);
        $url = $url . "?cmd=". APP_ID . "&interface=Mix_StreamV2&t=".strval($time_now)."&sign=".strval($md5_sign);
      	$component_proxy=new Component_Video_Proxy(1, $config, $url);
        $ret = $component_proxy->call($para, $result, $error_message, "POST");

        $this->_retValue = EC_OK;
        $this->_data = array(
            'msg' =>json_encode($result)
        );

        interface_log(INFO, EC_OK, 'MergeVideoStream::process() succeed');
        return true;
    }
}

?>
