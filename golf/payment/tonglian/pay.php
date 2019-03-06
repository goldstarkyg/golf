<?php
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require_once 'AppConfig.php';
require_once 'AppUtil.php';
require_once 'com.fun.php';

$sl = $_GPC['ps'];
$params = @json_decode(base64_decode($sl), true);
$_SESSION['openid'] = $params['user'];
$_W['openid'] 		= $_SESSION['openid'];
$_SESSION['oauth_openid'] = $_W['openid'];

require '../../app/common/bootstrap.app.inc.php';

load()->app('common');
load()->app('template');

if($_GPC['done'] == '1') {
	$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
	$pars = array();
	$pars[':plid'] = $params['tid'];
	$log = pdo_fetch($sql, $pars);
	if(!empty($log)) {
		if (!empty($log['tag'])) {
			$tag = iunserializer($log['tag']);
			$log['uid'] = $tag['uid'];
		}
		$site = WeUtility::createModuleSite($log['module']);
		if(!is_error($site)) {
			$method = 'payResult';
			if (method_exists($site, $method)) {
				$ret = array();
				$ret['weid'] = $log['uniacid'];
				$ret['uniacid'] = $log['uniacid'];
				$ret['result'] = 'success';
				$ret['type'] = $log['type'];
				$ret['from'] = 'return';
				$ret['tid'] = $log['tid'];
				$ret['uniontid'] = $log['uniontid'];
				$ret['user'] = $log['openid'];
				$ret['fee'] = $log['fee'];
				$ret['tag'] = $tag;
				$ret['is_usecard'] = $log['is_usecard'];
				$ret['card_type'] = $log['card_type'];
				$ret['card_fee'] = $log['card_fee'];
				$ret['card_id'] = $log['card_id'];
				exit($site->$method($ret));
			}
		}
	}
}

$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
$log = pdo_fetch($sql, array(':plid' => $params['tid']));
if(!empty($log) && $log['status'] != '0') {
	exit('这个订单已经支付成功, 不需要重复支付.');
}
$auth = sha1($sl . $log['uniacid'] . $_W['config']['setting']['authkey']);
if($auth != $_GPC['auth']) {
	exit('参数传输错误.');
}

$_W['uniacid'] = intval($log['uniacid']);
$_W['openid'] = intval($log['openid']);
$setting = uni_setting($_W['uniacid'], array('payment'));
if(!is_array($setting['payment'])) {
	exit('没有设定支付参数.');
}


$tl_params = array();
$tl_params["cusid"] 	= AppConfig::CUSID;
$tl_params["appid"] 	= AppConfig::APPID;
$tl_params["version"] 	= AppConfig::APIVERSION;
$tl_params["trxamt"] 	= $log['card_fee']*100;

$tl_params["reqsn"] 	= $log['tid'];//订单号,自行生成
$tl_params["paytype"] 	= "W02";
$tl_params["randomstr"] = random(10,true);//
$tl_params["body"] 		= $params['title'];
$tl_params["remark"] 	= $_W['uniacid'];
$tl_params["acct"] 		= $params['user'];
$tl_params["limit_pay"] = "no_credit";
$tl_params["notify_url"]= $_W['siteroot'] . 'notify.php';
$tl_params["sign"] 		= AppUtil::SignArray($tl_params,AppConfig::APPKEY);//签名

$paramsStr = AppUtil::ToUrlParams($tl_params);
$url = AppConfig::APIURL . "/pay";
$rsp = request($url, $paramsStr);

$rspArray = json_decode($rsp, true);
if(!validSign($rspArray)){
	message("抱歉，发起支付失败，具体原因为：“签名校验失败”。请及时联系站点管理员。");exit;
}

$package = json_decode($rspArray['payinfo'],true);

?>
<html>

<head>
	<!-- <link rel="icon" href="/front/image/bitbug_favicon.ico" type="image/x-icon"> -->
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=8">
	<meta http-equiv="Expires" content="0">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Cache-control" content="no-cache">
	<meta http-equiv="Cache" content="no-cache">
	<meta name="format-detection" content="telephone=no">
	<title>支付页面</title>
</head>

<body >
	请稍等，加载中。。。。。。。。。
</body>

</html>

<script type="text/javascript">
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		WeixinJSBridge.invoke('getBrandWCPayRequest', {
			'appId' : '<?php echo $package['appId'];?>',
			'timeStamp': '<?php echo $package['timeStamp'];?>',
			'signType' : '<?php echo $package['signType'];?>',
			'package' : '<?php echo $package['package'];?>',
			'nonceStr' : '<?php echo $package['nonceStr'];?>',
			'paySign' : '<?php echo $package['paySign'];?>',
		}, function(res) {
			if(res.err_msg == 'get_brand_wcpay_request:ok') {
				location.search += '&done=1';
			} else {
//				err_desc
				alert('启动微信支付失败');
//				alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
				history.go(-2);
			}
		});
	}, false);
</script>
