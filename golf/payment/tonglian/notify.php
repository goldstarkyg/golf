<?php
	define('IN_MOBILE', true);
	require '../../framework/bootstrap.inc.php';
	require_once 'AppConfig.php';
	require_once 'AppUtil.php';

	$params = array();
	foreach($_POST as $key=>$val) {//动态遍历获取所有收到的参数,此步非常关键,因为收银宝以后可能会加字段,动态获取可以兼容由于收银宝加字段而引起的签名异常
		$params[$key] = $val;
	}

/**************************************  params ******************************************
	acct : oyDjJv3Hkt1EHBk7fY9vN5RgqwJk ;
	appid : 00012263 ;
	chnltrxid : 4002972001201708186912791867 ;
	cusid : 372581080998436 ;
	cusorderid : 2017081816563321793084 ;
	outtrxid : 	2017081816563321793084 ;
	paytime : 20170818165708 ;
	sign : 090D92647E10E9AD66BDD69412E6959C ;
	termauthno : CFT ;
	termrefnum : 4002972001201708186912791867 ;
	termtraceno : 0 ;
	trxamt : 1 ;
	trxcode : VSP501 ;
	trxdate : 20170818 ;
	trxid : 111708890000014062 ;
	trxreserved : 28 ;
	trxstatus : 0000 ;
**************************************  params ******************************************/



	if(count($params)<1){//如果参数为空,则不进行处理
		echo "error";
		exit();
	}



	if(AppUtil::ValidSign($params, AppConfig::APPKEY)){//验签成功

		//此处进行业务逻辑处理
/****************************************  从微擎的异步通知复制过来的代码  ************************************/
		$_W['uniacid'] = $_W['weid'] = $params['trxreserved'];
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
		$sql_params = array();
		$sql_params[':uniontid'] = $params['cusorderid'];
		$log = pdo_fetch($sql, $sql_params);

		WeUtility::logging('tonglian_notify_return_log', $log);
		if(!empty($log) && $log['status'] == '0') {
			$log['tag']                   = iunserializer($log['tag']);
			$log['tag']['transaction_id'] = $params['termrefnum'];
			$log['uid']                   = $log['tag']['uid'];
			$log['transaction_id']        = $params['termrefnum'];
			$record                       = array();
			$record['status']             = '1';
			$record['tag']                = iserializer($log['tag']);
			pdo_update('core_paylog', $record, array('plid' => $log['plid']));

			// 不会使用到卡券的
	//		if ($log['is_usecard'] == 1 && $log['card_type'] == 1 && !empty($log['encrypt_code']) && $log['acid']) {
	//			load()->classs('coupon');
	//			$acc                     = new coupon($log['acid']);
	//			$codearr['encrypt_code'] = $log['encrypt_code'];
	//			$codearr['module']       = $log['module'];
	//			$codearr['card_id']      = $log['card_id'];
	//			$acc->PayConsumeCode($codearr);
	//		}
	//		if ($log['is_usecard'] == 1 && $log['card_type'] == 2) {
	//			$now            = time();
	//			$log['card_id'] = intval($log['card_id']);
	//			pdo_query('UPDATE ' . tablename('activity_coupon_record') . " SET status = 2, usetime = {$now}, usemodule = '{$log['module']}' WHERE uniacid = :aid AND couponid = :cid AND uid = :uid AND status = 1 LIMIT 1", array(':aid' => $_W['uniacid'], ':uid' => $log['uid'], ':cid' => $log['card_id']));
	//		}

			$site = WeUtility::createModuleSite($log['module']);
			if(!is_error($site)) {
				$method = 'payResult';
				if (method_exists($site, $method)) {
					$ret = array();
					$ret['weid'] 	= $log['uniacid'];
					$ret['uniacid'] = $log['uniacid'];
					$ret['acid'] 	= $log['acid'];
					$ret['result'] 	= 'success';
					$ret['type'] 	= $log['type'];
					$ret['from'] 	= 'notify';
					$ret['tid'] 	= $log['tid'];
					$ret['uniontid']= $log['outtrxid'];
					$ret['transaction_id'] 	= $log['transaction_id'];
					$ret['trade_type'] 		= $params['trade_type'];
					$ret['follow'] 			= 1;
					$ret['user'] 			= empty($params['acct']) ? $log['openid'] : $params['acct'];
					$ret['fee'] 			= $log['fee'];
					$ret['tag'] 			= $log['tag'];
					$ret['is_usecard'] 		= $log['is_usecard'];
					$ret['card_type'] 		= $log['card_type'];
					$ret['card_fee'] 		= $log['card_fee'];
					$ret['card_id'] 		= $log['card_id'];
//					if(!empty($get['time_end'])) {
//						$ret['paytime'] = strtotime($get['time_end']);
//					}
					$site->$method($ret);
/****************************************  从微擎的异步通知复制过来的代码  ************************************/
				}
			}
			echo "success";
		}


	}
	else{
		echo "erro";
	}

?>
