<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

//create token when register
function create_token() {
    $ret  = array();
    $now    =  strtotime("now");
    $expired = $now + 60*60*12; // 12 hours expired
    $token  = base64_encode(random(10).random(10).$now);
    $ret['starttime'] = $now;
    $ret['expired']   = $expired;
    $ret['token']     = $token;
    return $ret;
}

//check current token
function compareToken(){
    global $_GPC;
    $token  = trim($_GPC['token']);
    $uid    = trim($_GPC['mid']);

    $sql = 'SELECT * FROM ' . tablename('mc_members') . ' WHERE `uid`=:uid and `token`=:token' ;
    $pars = array();
    $pars[':uid']   = $uid;
    $pars[':token'] = $token;
    $user = pdo_fetch($sql, $pars);
    if(!empty($user)) {
        $now    =  strtotime("now");
        $expired = $user['expired'];
        if($now > $expired) {
            return 'false';
        }else{
            return 'true';
        }
    }else {
        return 'false';
    }
}














