<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('register', 'login', 'forgot','forward','login','sendfocus','timerank','rank','getfan')) ? $do : 'register';

//$openid = $_W['openid'];
//$dos = array('register', 'uc');

//$setting = uni_setting($_W['uniacid'], array('uc', 'passport'));
//$uc_setting = $setting['uc'] ? $setting['uc'] : array();
//$item = empty($setting['passport']['item']) ? 'random' : $setting['passport']['item'];
//$audit = intval($setting['passport']['audit']);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$public_path = $protocol.$domain;


$forward = url('mc'); //./index.php?i=0&j=&c=mc&

$ret = array();

if($do == 'register') {
    global $_W;
    $userid         = trim($_GPC['userid']); //phone
    $nickname       = trim($_GPC['nickname']);
    $gender         = trim($_GPC['gender']); //gender 男 女
    if($gender == '男') $gender = '1';
    if($gender == '女') $gender = '2';
    $phone          = trim($_GPC['phone']); //phone

    //$email          = trim($_GPC['email']);
    $email          = '';
    $mobilenumber   = $phone;
    $password       = trim($_GPC['password']);
    //$password       = '123456789';
    $uniacid        = trim($_GPC['uniacid']);
    $_W['uniacid']  = $uniacid;//8


    //get check duplicate tencentuid
    $user_inform = pdo_fetchcolumn('SELECT * FROM ' .tablename('mc_members') . ' WHERE uniacid = :uniacid AND tencentuserid = :tencentuserid', array(':uniacid' => $_W['uniacid'],':tencentuserid'=>$userid));
    if(!empty($user_inform)) {
        $ret['code']        = '408';
        $ret['message']     = 'This phone was registered already'; //duplicate user
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

    $data['email']      = $email;
    $data['mobile']     = $mobilenumber;
    $data['password']   = md5($password . $data['salt'] . $_W['config']['setting']['authkey']);
    $data['nickname']   = $nickname;
    $data['realname']   = $nickname;
    $data['tencentuserid'] = $userid;
    $data['uniacid']    = $uniacid;
    $data['gender']    = $gender;
    $data['vip']        = '1';

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

}

if($do == 'login') {
    global $_W;
    $username = trim($_GPC['userid']);
    $_W['uniacid'] = $_GPC['uniacid'];
    $password = trim($_GPC['password']);


    $sql = 'SELECT `uid`,`salt`,`password` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid AND `tencentuserid`=:tencentuserid and `vip`=:vip ' ;
    $pars = array();
    $pars[':uniacid']       = $_W['uniacid'];
    $pars[':tencentuserid'] = $username;
    $pars[':vip']           = '1';
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

if($do == 'sendfocus') {
    global $_GPC;
    $vip_userid      = trim($_GPC['vip_userid']);
    $host_userid     = trim($_GPC['host_userid']);
    $uniacid         = trim($_GPC['uniacid']);
    $confirm_sql = "select * from ims_mc_member_focus where vip_userid = '".$vip_userid."' and host_userid = '".$host_userid."' ";
    $confirm = pdo_fetch($confirm_sql);
    if(empty($confirm)) {
        $data = array(
            'host_userid' => $host_userid,
            'vip_userid' => $vip_userid
        );
        pdo_insert_table('ims_mc_member_focus', $data);
    }
    //get host user informaltion

        $base_sql = "select ld.* from live_data ld join ims_mc_members mm on ld.userid = mm.tencentuserid where status = 1 and mm.tencentuserid = '".$host_userid."' ";
        $query_sql = $base_sql . "  order by ld.create_time desc";
        $list = pdo_fetch($query_sql);
        if(empty($list)) {
            $ret['code']        = '200';
            $ret['message']     = array();//'There is no date like this host user.';
            echo json_encode($ret);
            return;
        }
        $live_list = array();

        if(!empty($list))
        {
                $groupid = '';
                $fileid = '';
                $record_type = 0;
                $desc = '';
                $forbid = '0';
                //get focus
                $focus = 0;
                $paid_focus = 0;
                $user_id = $list['userid'];
                $focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '".$host_userid."' " ;
                $focus_result = pdo_fetch($focus_sql);
                if(!empty($focus_result)) {
                    $focus = intval($focus_result['focus_count']);
                }

                $paid_focus_sql = "select count(*) as focus_count from ims_mc_member_focus fo join ims_mc_members mm on fo.host_userid = mm.tencentuserid  where fo.host_userid = '".$host_userid."' and mm.coin > 0 " ;
                $paid_focus_result = pdo_fetch($paid_focus_sql);;
                if(!empty($paid_focus_result)) {
                    $paid_focus = intval($paid_focus_result['focus_count']) ;
                }
                $one_record = array(
                    'userid' => $list['userid'],
                    'groupid' =>$groupid,
                    'timestamp' => strtotime($list['create_time']) ? strtotime($list['create_time']) : 0,
                    'type' => $record_type,
                    'viewercount' => intval($list['viewer_count']),
                    'likecount' => intval($list['like_count']),
                    'title' => $list['title'],
                    'playurl' => $list['play_url'],
                    'hls_play_url' => $list['hls_play_url'],
                    'desc' => $desc,
                    'data_type' => $type,
                    'forbid_status' => intval($forbid),
                    'status' => $list['status'],
                    'fileid' => $fileid,
                    'userinfo' => array(
                        'nickname' => $list['nickname'],
                        'headpic'  => $list['headpic'],
                        'frontcover' => $list['frontcover'],
                        'location' => $list['location'],
                    ),
                    'focuscount' => $focus,
                    'paidfocuscount' => $paid_focus,
                );
                array_push($live_list,$one_record);

        }

    $ret['code']        = '200';
    $ret['message']     = '';
    $ret['content']     = $live_list;
    echo json_encode($ret);
    return;
}

if($do == 'timerank') {
    global $_GPC;
    $date = $_GPC['date'];
    $uniacid = $_GPC['uniacid'];
    $vip_userid = $_GPC['vip_userid'];

    $date = date('Y-m-d H:i:s' ,strtotime($date));
    $sql  = " SELECT sum(coin) as coin, host_userid  FROM " . tablename('mc_member_coin_history') . " WHERE created_at >= '".$date."' group by host_userid ORDER BY coin DESC";
    $result = pdo_fetchall($sql);

    if(empty($result)) {
        $ret['code']        = '200';
        $ret['message']     = array();//'There is no data.' ;
        echo json_encode($ret);
        return;
    }

    $ranks = array();
    foreach ( $result as $list) {
        $one = array();
            $get_coin       = $list['coin'] ;
            if(empty($get_coin)) $get_coin = 0;
            $one['get_coin']= $get_coin;

            $user_id = $list['host_userid'];
            $one['host_userid'] = $user_id;

            $focus_sql  = "select * from ims_mc_member_focus where host_userid='".$user_id."' and  vip_userid = '".$vip_userid."'";
            $focus      = pdo_fetch($focus_sql);
            $focus_status = 0;
            if(!empty($focus)) $focus_status = 1;
            $one['focus_status'] = $focus_status;

            $user_sql = " SELECT *, mm.nickname as hostname   FROM " . tablename('mc_members') . " mm left join live_data ld on mm.tencentuserid = ld.userid  WHERE mm.tencentuserid = '" . $user_id . "' and mm.uniacid = '" . $uniacid . "'";
            $user = pdo_fetch($user_sql);
            //$faceimage = $public_path . '/golf/attachment/' . $user['avatar'];
            $faceimage = $user['headpic'];
            if(empty($user['headpic'])) $faceimage = '';
            $one['face_image'] = $faceimage;

            $host_name = $user['hostname'];
            if(empty($host_name)) $host_name = "";
            $one['host_name'] = $host_name;

            $category_id = $user['pcate'];
            $category_sql = " SELECT *  FROM " . tablename('golf_category') . " WHERE id = '" . $category_id . "' and uniacid='" . $uniacid . "'";
            $category = pdo_fetch($category_sql);
            $host_category = $category['name'];
            if(empty($host_category)) $host_category = "";
            $one['host_category'] = $host_category;

            $live_sql = " SELECT *  FROM " . 'live_data' . " WHERE userid = '" . $user_id . "'";
            $live = pdo_fetch($live_sql);
            $viwer_number = $live['viewer_count'];
            if(empty($live['viewer_count'])) $viwer_number = 0;
            $one['viwer_number'] = $viwer_number;

            array_push($ranks, $one);
    }

    $ret['code']        = '200';
    $ret['content']     = $ranks ;
    echo json_encode($ret);
    return;
}

if($do == 'rank') {
    global $_GPC;
    $date = $_GPC['date'];
    $uniacid = $_GPC['uniacid'];
    $vip_userid = $_GPC['vip_userid'];
    $date = date('Y-m-d H:i:s' ,strtotime($date));

    $sql  = " SELECT sum(coin) as coin, host_userid  FROM " . tablename('mc_member_coin_history') . "  group by host_userid ORDER BY coin DESC";
    $result = pdo_fetchall($sql);
    if(empty($result)) {
        $ret['code']        = '200';
        $ret['content']     = array(); //there is no data
        echo json_encode($ret);
        return;
    }
    $ranks = array();
    foreach ( $result as $list) {
        $one = array();
            $get_coin       = $list['coin'] ;
            if(empty($get_coin)) $get_coin = '0';
            $one['get_coin']= $get_coin;

            $host_userid = $list['host_userid'];
            $one['host_userid'] = $host_userid;
            $user_sql = " SELECT *  FROM " . tablename('mc_members') . " WHERE tencentuserid = '" . $host_userid . "' and uniacid = '" . $uniacid . "'";
            $user = pdo_fetch($user_sql);
            $faceimage = $public_path . '/golf/attachment/' . $user['avatar'];
            if(empty($user['avatar'])) $faceimage = '';
            $one['face_image'] = $faceimage;

            $host_name = $list['nickname'];
            if(empty($host_name)) $host_name = '';
            $one['host_name'] = $host_name;

            $category_id = $list['pcate'];
            $category_sql = " SELECT *  FROM " . tablename('golf_category') . " WHERE id = '" . $category_id . "' and uniacid='" . $uniacid . "'";
            $category = pdo_fetch($category_sql);
            $host_category = $category['name'];
            if(empty($host_category)) $host_category = "";
            $one['host_category'] = $host_category;

            $fan_sql = " SELECT count(*) fan_count  FROM " . 'ims_mc_member_focus' . " WHERE host_userid = '" . $host_userid . "'";
            $fan = pdo_fetch($fan_sql);
            $fan_number = $fan['fan_count'];
            if(empty($fan_number)) $fan_number = '0';
            $one['fan_number'] = $fan_number;

            $fan_status_sql = " SELECT count(*) fan_count  FROM " . 'ims_mc_member_focus' . " WHERE host_userid = '" . $host_userid . "' and vip_userid='".$vip_userid."'";
            $fan_status = pdo_fetch($fan_status_sql);
            $fan_status_flag = $fan_status['fan_count'];
            if(empty($fan_status_flag)) $fan_status_flag = '0';
            else $fan_status_flag = '1';
            $one['fan_status'] = $fan_status_flag;

            $live_sql = " SELECT *  FROM " . 'live_data' . " WHERE userid = '" . $host_userid . "'";
            $live = pdo_fetch($live_sql);
            $status = $live['status'];
            if(empty($status)) $status = '0';
            $one['live_status'] = $status;

            //get this weeks coin count;
            $date = date('Y-m-d', strtotime('-7 days'));
            $week_sql   = " SELECT count(*) as week_number  FROM " . 'ims_mc_member_focus' . " WHERE created_at >= '".$date."' and host_userid = '".$host_userid."' ";
            $week       = pdo_fetch($week_sql);
            $week_count = $week['week_number'];
            if(empty($week_count)) $week_count = 0;
            //get last weeks coin count;
            $last_date = date('Y-m-d', strtotime('-14 days'));
            $this_date = date('Y-m-d', strtotime('-7 days'));
            $last_week_sql  = " SELECT count(*) as week_number  FROM " . 'ims_mc_member_focus' . " WHERE created_at >= '".$last_date."' and created_at < '".$this_date."' and host_userid = '".$host_userid."' ";
            $last_week = pdo_fetch($last_week_sql);
            $last_week_count = $last_week['week_number'];
            if(empty($last_week_count)) $last_week_count = 0;
            $compare_week = 0;
            if(intval($week_count) > intval($last_week_count)) {
                $compare_week = '1';
            }
            if(intval($week_count) < intval($last_week_count))  {
                $compare_week = '0';
            }
            if(intval($week_count) == intval($last_week_count)) {
                $compare_week = '2';
            }
            $one['compare_week'] = $compare_week ;
            array_push($ranks, $one);
    }
    $ret['code']        = '200';
    $ret['content']     = $ranks ;
    echo json_encode($ret);
    return;
}

if($do == 'getfan') {

    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $host_userid    = $_GPC['host_userid'];
    $fan_sql = " SELECT  vip_userid FROM " . 'ims_mc_member_focus' . " WHERE host_userid = '" . $host_userid . "' group by host_userid ";
    $fan_result = pdo_fetchall($fan_sql);

    if(empty($fan_result)) {
        $ret['code']        = '401';
        $ret['content']     = 'There is no data.' ;
        echo json_encode($ret);
        return;
    }
    $items = array();
    foreach ( $fan_result as $list) {
        $one = array();
        $vip_userid = $list['vip_userid'];
        $coin_sql  = " SELECT sum(coin) FROM " . tablename('mc_member_coin_history') . "  where vip_userid= '".$vip_userid."' ";
        $coin_result = pdo_fetch($coin_sql);

        if(!empty($coin_result)) {
            $one['vip_userid'] = $vip_userid;

            $user_sql = " SELECT *  FROM " . tablename('mc_members') . " WHERE tencentuserid = '" . $vip_userid . "' and uniacid = '" . $uniacid . "'";
            $user = pdo_fetch($user_sql);
            $faceimage = $public_path . '/golf/attachment/' . $user['avatar'];
            if (empty($user['avatar'])) $faceimage = '';
            $one['face_image'] = $faceimage;

            $vip_username = $user['nickname'];
            if (empty($vip_username)) $vip_username = '';
            $one['vip_username'] = $vip_username;

            $gender = $user['gender'];
            if (empty($gender)) $gender = '';
            if($gender == '1') $gender ='男';
            if($gender == '2') $gender ='女';
            $one['gender'] = $gender;
            $items[] = $one;
        }
    }
    $ret['code']        = '200';
    $ret['content']     = $items ;
    echo json_encode($ret);
    return;
}
