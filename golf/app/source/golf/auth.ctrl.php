<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('register', 'login', 'forgot','forward','login')) ? $do : 'register';

$openid = $_W['openid'];
$dos = array('register', 'uc');

$setting = uni_setting($_W['uniacid'], array('uc', 'passport'));
$uc_setting = $setting['uc'] ? $setting['uc'] : array();
$item = empty($setting['passport']['item']) ? 'random' : $setting['passport']['item'];
$audit = intval($setting['passport']['audit']);

$forward = url('mc'); //./index.php?i=0&j=&c=mc&

$ret = array();

if($do == 'register') {
    $username = trim($_GPC['username']);
    $code = trim($_GPC['code']);
    $password = trim($_GPC['password']);
    $repassword = trim($_GPC['repassword']);
    if($repassword != $password )
    {
        $ret['code']        = '403';
        $ret['message']     = '两次密码输入不一致'; // Compare  error  a  password  and  re password
        echo json_encode($ret);
        return;
    }
    $sql = 'SELECT `uid` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid';
    $pars = array();
    $pars[':uniacid'] = $_W['uniacid'];
    if($item == 'email') {
        if(preg_match(REGULAR_EMAIL, $username)) {
            $type = 'email';
            $sql .= ' AND `email`=:email';
            $pars[':email'] = $username;
        } else {
            $ret['code']        = '404';
            $ret['message']     = '邮箱格式不正确'; //- Email Type error
            echo json_encode($ret);
            return;
        }
    } elseif($item == 'mobile') {
        if(preg_match(REGULAR_MOBILE, $username)) {
            $type = 'mobile';
            $sql .= ' AND `mobile`=:mobile';
            $pars[':mobile'] = $username;
        } else {
            $ret['code']        = '405';
            $ret['message']     = '手机号格式不正确'; // Mobile type error
            echo json_encode($ret);
            return;
        }
    } else {
        if(preg_match(REGULAR_MOBILE, $username)) {
            $type = 'mobile';
            $sql .= ' AND `mobile`=:mobile';
            $pars[':mobile'] = $username;
        } elseif(preg_match(REGULAR_EMAIL, $username)) {
            $type = 'email';
            $sql .= ' AND `email`=:email';
            $pars[':email'] = $username;
        } else {
            $ret['code']        = '406';
            $ret['message']     = '您输入的用户名格式错误';//Mobile or Email  type error
            echo json_encode($ret);
            return;
        }
    }

    if($audit == 1) {
        load()->model('utility');
        if(!code_verify($_W['uniacid'], $post['username'], $post['code'])) {
            $ret['code']        = '407';
            $ret['message']     = '验证码错误'; //verification code error
            echo json_encode($ret);
            return;
        }
    }

    $user = pdo_fetch($sql, $pars); //$pars => {":uniacid":0,":mobile":"18841568752"}
    if(!empty($user)) {
        $ret['code']        = '408';
        $ret['message']     = '该用户名已被注册，请输入其他用户名。'; //duplicated user error
        echo json_encode($ret);
        return;
    }
    if(!empty($_W['openid'])) {
        $fan = mc_fansinfo($_W['openid']);
        if (!empty($fan)) {
            $map_fans = $fan['tag'];
        }
        if (empty($map_fans) && isset($_SESSION['userinfo'])) {
            $map_fans = unserialize(base64_decode($_SESSION['userinfo']));
        }
    }

    $default_groupid = pdo_fetchcolumn('SELECT groupid FROM ' .tablename('mc_groups') . ' WHERE uniacid = :uniacid AND isdefault = 1', array(':uniacid' => $_W['uniacid']));

    $data = array(
        'uniacid' => $_W['uniacid'],
        'salt' => random(8),
        'groupid' => $default_groupid,
        'createtime' => TIMESTAMP,
    );

    $data['email']  = $type == 'email'  ? $username : '';
    $data['mobile'] = $type == 'mobile' ? $username : '';
    $data['password'] = md5($password . $data['salt'] . $_W['config']['setting']['authkey']);

    if(!empty($map_fans)) {
        $data['nickname'] = $map_fans['nickname'];
        $data['gender'] = $map_fans['sex'];
        $data['residecity'] = $map_fans['city'] ? $map_fans['city'] . '市' : '';
        $data['resideprovince'] = $map_fans['province'] ? $map_fans['province'] . '省' : '';
        $data['nationality'] = $map_fans['country'];
        $data['avatar'] = rtrim($map_fans['headimgurl'], '0') . 132;
    }

    load()->app('golf');
    $token = create_token();
    if(!empty($token)) {
        $data['starttime']  = $token['starttime'];
        $data['expired']    = $token['expired'];
        $data['token']      = $token['token'];
    }

    pdo_insert('mc_members', $data);
    $user['uid'] = pdo_insertid();

    if (!empty($fan) && !empty($fan['fanid'])) {
        pdo_update('mc_mapping_fans', array('uid'=>$user['uid']), array('fanid'=>$fan['fanid']));
    }
    if(_mc_login($user)) {
        $sel = array();
        $sel[':uid'] = $user['uid'];
        $sql = 'SELECT `token` FROM ' . tablename('mc_members') . ' WHERE `uid`=:uid';
        $user['token']      = pdo_fetch($sql, $sel );
        $ret['code']        = '200';
        $ret['message']     = '';
        $ret['content']     = $user;
        echo json_encode($ret);
        return;
    }
    $ret['code']        = '409';
    $ret['message']     = '未知错误导致注册失败'; //unknown  other error
    echo json_encode($ret);
    return;
    
}else if($do == 'login') {
        global $_W;
        $username = trim($_GPC['userid']);
        $_W['uniacid'] = $_GPC['uniacid'];
        //$password = trim($_GPC['password']);
        $password = '123456789';

        $sql = 'SELECT `uid`,`salt`,`password` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid AND `tencentuserid`=:tencentuserid and `vip`=:vip ' ;
        $pars = array();
        $pars[':uniacid']       = $_W['uniacid'];
        $pars[':tencentuserid'] = $username;
        $pars[':vip']           = '0';
        /*if(preg_match(REGULAR_MOBILE, $username)) {
            $sql .= ' AND `mobile`=:mobile';
            $pars[':mobile'] = $username;
        } else {
            $sql .= ' AND `email`=:email';
            $pars[':email'] = $post['username'];
        }*/
        $user = pdo_fetch($sql, $pars);
        if(empty($user)) {
            $ret['code']        = '411';
            $ret['message']     = '不存在该账号的用户资料'; //Empty User
            echo json_encode($ret);
            return;
        }

        $hash = md5($password . $user['salt'] . $_W['config']['setting']['authkey']);
        if($user['password'] != $hash) {
            $ret['code']        = '410';
            $ret['message']     = '密码错误'; //Wrong password
            echo json_encode($ret);
            return;
        }

        if(_mc_login($user)) {
            load()->app('golf');
            $data = create_token();
            $starttime  = $data['starttime'];
            $expired    = $data['expired'];
            $token      = $data['token'];

            $uid = $user['uid'];
            $sql = "update " . tablename('mc_members') . " set starttime='".$starttime."' , expired ='".$expired."', token ='".$token."'   where uid=:uid";
            pdo_query($sql, array(":uid" => $uid));

            $sel = array();
            $sel[':uid'] = $user['uid'];
            $sql = 'SELECT *  FROM ' . tablename('mc_members') . ' WHERE `uid`=:uid';
            $user= pdo_fetch($sql, $sel );

            $ret['code']        = '200';
            $ret['message']     = 'success!';
            $ret['content']     = $user;
            echo json_encode($ret);
            return;
        }

        $ret['code']        = '409';
        $ret['message']     = '未知错误导致登陆失败'; //unknown  other error
        echo json_encode($ret);
        return;
}
