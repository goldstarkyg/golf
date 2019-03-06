<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付样例</title>
    <style type="text/css">
        ul {
            margin-left:10px;
            margin-right:10px;
            margin-top:10px;
            padding: 0;
        }
        li {
            width: 32%;
            float: left;
            margin: 0px;
            margin-left:1%;
            padding: 0px;
            height: 100px;
            display: inline;
            line-height: 100px;
            color: #fff;
            font-size: x-large;
            word-break:break-all;
            word-wrap : break-word;
            -marginbottom: 5px;
        }
        a {
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:link{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:visited{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:hover{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
        a:active{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        	text-decoration:none;
            color:#fff;
        }
    </style>
</head>
<body>
	<div align="center">
        <ul>
            <li style="background-color:#FF7F24"><a href="http://paysdk.weixin.qq.com/example/jsapi.php">JSAPI支付</a></li>
            <li style="background-color:#698B22"><a href="http://paysdk.weixin.qq.com/example/micropay.php">刷卡支付</a></li>
            <li style="background-color:#8B6914"><a href="http://paysdk.weixin.qq.com/example/native.php">扫码支付</a></li>
            <li style="background-color:#CDCD00"><a href="http://paysdk.weixin.qq.com/example/orderquery.php">订单查询</a></li>
            <li style="background-color:#CD3278"><a href="http://paysdk.weixin.qq.com/example/refund.php">订单退款</a></li>
            <li style="background-color:#848484"><a href="http://paysdk.weixin.qq.com/example/refundquery.php">退款查询</a></li>
            <li style="background-color:#8EE5EE"><a href="http://paysdk.weixin.qq.com/example/download.php">下载订单</a></li>
        </ul>
	</div>
</body>
</html>
<?php

function array2xml($arr, $level = 1) {
    $s = $level == 1 ? "<xml>" : '';
    foreach ($arr as $tagname => $value) {
        if (is_numeric($tagname)) {
            $tagname = $value['TagName'];
            unset($value['TagName']);
        }
        if (!is_array($value)) {
            $s .= "<{$tagname}>" . (!is_numeric($value) ? '' : '') . $value . (!is_numeric($value) ? '' : '') . "</{$tagname}>";
        } else {
            $s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    return $level == 1 ? $s . "</xml>" : $s;
}

function random($length, $numeric = FALSE) {
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}


/*$package = array();
$package['appid'] = 'wx2704af054a7e345d';
$package['body'] = 'APP支付测试';
$package['mch_id'] = '1263336301';
$package['nonce_str'] = 'f6eb22182fd7418d16aaec280fddbd6d';
$package['notify_url'] = 'http://www.weixin.qq.com/wxpay/pay.php';
$package['out_trade_no'] = '20150806125346';
$package['spbill_create_ip'] = '45.56.152.213';
$package['total_fee'] = 1 * 100;
$package['trade_type'] = 'APP';
//ksort($wOpt, SORT_STRING);
$string1 = '';
foreach ($package as $key => $v) {
    if (empty($v)) {
        continue;
    }
    $string1 .= $key . '=' . $v . '&';
}
//$string1 .= 'key=' . '4uGfoMVpKSqAXXTtQgMOG1cWFpZCmoTv';
$package['sign'] = strtoupper(md5($string1));
$dat = array2xml($package);
echo $dat;*/
define('TIMESTAMP', time());
$package = array();
$package['appid'] = 'wx2704af054a7e345d';
$package['mch_id'] = '1263336301';
$package['nonce_str'] = random(8);
$package['body'] = 'test';
$package['attach'] = '8';
$package['out_trade_no'] = '20150806125346';
$package['total_fee'] = 1 * 100;
$package['spbill_create_ip'] = '45.56.152.156';
$package['time_start'] = '20180525091010';
$package['time_expire'] = '20180725091010';
$package['notify_url'] = 'http://localhost/example/notify.php';
$package['trade_type'] = 'NATIVE';
//$package['openid'] = '1604';
ksort($package, SORT_STRING);
$string1 = '';
foreach($package as $key => $v) {
    if (empty($v)) {
        continue;
    }
    $string1 .= "{$key}={$v}&";
}
$string1 .= "key=4uGfoMVpKSqAXXTtQgMOG1cWFpZCmoTv";
$package['sign'] = strtoupper(md5($string1));
$dat = array2xml($package);
echo $dat;
/*
 <xml>
	<appid>wx2704af054a7e345d</appid>
	<attach>8</attach>
	<body>test</body>
	<mch_id>1263336301</mch_id>
	<nonce_str>gIvqUcV7</nonce_str>
	<notify_url>http://localhost/example/notify.php</notify_url>
	<out_trade_no>20150806125346</out_trade_no>
	<spbill_create_ip>45.56.152.156</spbill_create_ip>
	<time_expire>20180725091010</time_expire>
	<time_start>20180525091010</time_start>
	<total_fee>100</total_fee>
	<trade_type>NATIVE</trade_type>
	<sign>2C05EA14E23B03E30F4DAC04EB7D9470</sign>
</xml>
 */
/*
partnerid
prepayid
noncestr
timestamp
package
sign
 */
?>


