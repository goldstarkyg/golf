<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('getprofile', 'getfocususer', 'getmyorder','getcoinhistory','getnicknamecount','changenickname',
                            'gethostinform','getshipaddress','getshipaddressinform','addshipaddress','del_ship_address','get_ship_address_default')) ? $do : 'getprofile';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$public_path = $protocol.$domain;

if($do == 'getprofile') {
    $ret = array();
    global $_GPC;
    $uniacid       = $_GPC['uniacid'];
    $userid        = $_GPC['userid'];
    //get user information
    $detail= pdo_fetch("SELECT gender, nickname, birthyear ,address  FROM " . tablename('mc_members') . " where tencentuserid='".$userid."'");
    $birthyear = $detail['birthyear'];
    if(!empty($birthyear) && $birthyear != 0) {
        $detail['age'] = date('Y') - $birthyear;
    }else{
        $detail['age'] = 0 ;
    }
    $gender = $detail['gender'];
    if(!empty($detail)) {
        if($gender == '0' ) $detail['gender'] = '男';
        if($gender == '1' ) $detail['gender'] = '男';
        if($gender == '2') $detail['gender'] = '女';
    }

    //get focus count like hsot user
    $focus_sql = "select count(*) focus_count from ims_mc_member_focus where vip_userid = '".$userid."' ";
    $focus = pdo_fetch($focus_sql);
    $detail['focus'] = $focus['focus_count'];
    //get focus count like vip user
    $member_sql = "select count(*) member_count from ims_mc_member_focus where host_userid = '".$userid."' ";
    $member = pdo_fetch($member_sql);
    $detail['member'] = $member['member_count'];
    $detail['label'] = "label";
    $ret['code'] = '200';
    $ret['content'] = $detail;
    echo json_encode($ret);
    return;
}
if($do == 'getfocususer') {
    $ret = array();
    global $_GPC;
    $uniacid       = $_GPC['uniacid'];
    $userid        = $_GPC['vip_userid'];
    //get user information
    $list= pdo_fetchall("SELECT * FROM " . tablename('mc_member_focus') . " where vip_userid='".$userid."'");
    $hostlist = array();
    foreach ($list as $li ) {
        $one = array();
        $host_userid = $li['host_userid'];
        $members = pdo_fetch("SELECT count(*) as member_count FROM " . tablename('mc_member_focus') . " where host_userid='".$host_userid."' ");
        if(!empty($members)) {
            $one['members'] = $members['member_count'];
        }else {
            $one['members'] = '0';
        }
        $host = pdo_fetch("SELECT ca.name as category  FROM ims_mc_members as me left join ims_golf_category as ca on me.pcate= ca.id   where me.tencentuserid='".$host_userid."' ");
        if(!empty($host)) {
            $one['category'] = $host['category'];
        }else {
            $one['category'] = '';
        }
        $live = pdo_fetch("SELECT nickname, headpic FROM live_data where userid='".$host_userid."' ");
        if(!empty($live)) {
            if(empty($live['nickname']) || $live['nickname'] == '') {
                $one['name'] = $host_userid;
            }else {
                $one['name'] = $live['nickname'];
            }
            $one['thumb'] = $live['headpic'];
        }
        array_push($hostlist, $one);
    }
    $ret['code'] = '200';
    $ret['content'] = $hostlist;
    echo json_encode($ret);
    return;
}
if($do == 'getmyorder') {
    global $_W, $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $_W['uniacid']  = $uniacid;
//    $user   = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
//    $uid    = $user['uid'];
    $from_user      = $vip_userid;

    $op = $_GPC['op'];
    if ($op == 'confirm') {
        $orderid = intval($_GPC['orderid']);
        $order = pdo_fetch("SELECT * FROM " . tablename('eso_sale_order') . " WHERE id = :id AND from_user = :from_user", array(':id' => $orderid, ':from_user' => $from_user));
        if (empty($order)) {
            $ret = array();
            $ret['code']    = '401';
            $ret['message'] = '抱歉，您的订单不存或是已经被取消！';//Sorry, your order is missing or has been cancelled!
            echo json_encode($ret);
            return;
        }
        pdo_update('eso_sale_order', array('status' => 3), array('id' => $orderid, 'from_user' => $from_user));
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = '确认收货完成！';//Confirm receipt completed!
        echo json_encode($ret);
        return;
    } else if ($op == 'detail') {

        $orderid = intval($_GPC['orderid']);
        $item = pdo_fetch("SELECT * FROM " . tablename('eso_sale_order') . " WHERE uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "' and id='{$orderid}' limit 1");
        if (empty($item)) {
            $ret = array();
            $ret['code']    = '402';
            $ret['message'] = '抱歉，您的订单不存或是已经被取消！';//CSorry, your order is missing or has been cancelled!
            echo json_encode($ret);
            return;
        }
        $goodsid = pdo_fetchall("SELECT goodsid,total FROM " . tablename('eso_sale_order_goods') . " WHERE orderid = '{$orderid}'", array(), 'goodsid');

        $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,o.optionid FROM " . tablename('eso_sale_order_goods') . " o left join " . tablename('eso_sale_goods') . " g on o.goodsid=g.id "
            . " WHERE o.orderid='{$orderid}'");
        foreach ($goods as &$g) {
            //属性
            $option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $g['optionid']));
            if ($option) {
                $g['title'] = "[" . $option['title'] . "]" . $g['title'];
                $g['marketprice'] = $option['marketprice'];
            }
        }
        unset($g);

        $dispatch = pdo_fetch("select id,dispatchname from " . tablename('eso_sale_dispatch') . " where id=:id limit 1", array(":id" => $item['dispatch']));
        $content = array();
        $content['item'] = $item;
        $content['goods'] = $goods;
        $content['dispatch'] = $dispatch;
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = 'sucess';
        $ret['content'] = $content;
        echo json_encode($ret);
        return;

    } else {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        $where = " uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "'";

        $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_order') . " WHERE $where ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(), 'id');
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('eso_sale_order') . " WHERE uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "'");
        $pager = pagination($total, $pindex, $psize);

        if (!empty($list)) {
            foreach ($list as &$row) {
                $goodsid = pdo_fetchall("SELECT goodsid,total FROM " . tablename('eso_sale_order_goods') . " WHERE orderid = '{$row['id']}'", array(), 'goodsid');
                $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,o.optionid FROM " . tablename('eso_sale_order_goods') . " o left join " . tablename('eso_sale_goods') . " g on o.goodsid=g.id "
                    . " WHERE o.orderid='{$row['id']}'");
                foreach ($goods as &$item) {
                    //属性
                    $option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));
                    if ($option) {
                        $item['title'] = "[" . $option['title'] . "]" . $item['title'];
                        $item['marketprice'] = $option['marketprice'];
                    }
                }
                unset($item);
                $row['goods'] = $goods;
                //$row['total'] = $goodsid;
                $row['dispatch'] = pdo_fetch("select id,dispatchname from " . tablename('eso_sale_dispatch') . " where id=:id limit 1", array(":id" => $row['dispatch']));
            }
        }

        //load()->model('mc');
        //$fans = mc_fetch($_W['member']['uid']);
        $content = array();
        foreach ($list as $li) {
            $good_list = $li['goods'];
            $goods = array();
            $status = $li['status'];
            $statusname = '';
            if($status == '-1') $statusname = '取消状态';
            if($status == '0') $statusname = '普通状态';
            if($status == '1') $statusname = '为已付款';
            if($status == '2') $statusname = '为已发货';
            if($status == '3') $statusname = '为成功';

            foreach ($good_list as $gl) {
                $gl['thumb'] = $public_path.'/golf/attachment/'.$gl['thumb'];
                $goods[] = $gl;
            }
            $li['statusname'] = $statusname;
            $li['goods'] = $goods;
            $content[] = $li;
        }
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = 'success';
        $ret['content'] = $content;
        echo json_encode($ret);
        return;
    }
}

if($do == 'getcoinhistory') {
    global $_W, $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;

    $ret = array();
    $detail = array();
    $user   = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
    if(!empty($user)) {
        $coin   = $user['coin'];
        $sql = " SELECT coin, option_type, created_at as created_date  FROM " . tablename('mc_member_coin_history') . " WHERE vip_userid ='".$vip_userid."' and uniacid= '".$uniacid."' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
        $list   = pdo_fetchall($sql) ;
        $count = 0;
        foreach ($list as $li) {
            $list[$count]['created_date'] = date('Y-m-d', strtotime($list[$count]['created_date']));
            $count++;
        }
        $detail['coin'] = $coin;
        $detail['list'] = $list;
    }
    $ret['code']    = '200';
    $ret['message'] = 'success';
    $ret['content'] = $detail;
    echo json_encode($ret);
    return;

}

if($do == 'getnicknamecount') {
    global $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $user   = pdo_fetch("SELECT nicknamecount, coin FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));

    $ret['code']    = '200';
    $ret['message'] = 'success';
    $ret['content'] = $user;
    echo json_encode($ret);
    return;
}

if($do == 'changenickname') {
    global $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $nickanme       = $_GPC['nickname'];
    $user   = pdo_fetch("SELECT nicknamecount, coin FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
    $nicknamecount  = $user['nicknamecount'];
    $coin           = $user['coin'];
    $coint_level = 0;
    if($nicknamecount == '0') $coint_level = 1;
    if($nicknamecount == '1') $coint_level = 10;
    if($nicknamecount == '2') $coint_level = 50;
    if($nicknamecount >= '3') $coint_level = 500;
    $coin = $coin - $coint_level;
    $nicknamecount++;
    ///////save history
    $data_coin = array(
        'coin'          => $coint_level,
        'vip_userid'    => $vip_userid,
        'host_userid'   => '0', //app
        'uniacid'       => $uniacid,
        'type'          => 'nickname',
        'option_type'   => 'out'
    );
    pdo_insert_table('ims_mc_member_coin_history', $data_coin);
    /////////
    $data = array(
        'nicknamecount' => $nicknamecount,
        'coin'          => $coin,
        'nickname'      => $nickanme
    );
    pdo_update('mc_members', $data, array('tencentuserid' => $vip_userid,'uniacid'=>$uniacid));
    $ret['code']    = '200';
    $ret['message'] = 'success';
    echo json_encode($ret);
    return;
}

if($do == 'gethostinform') {
    $ret = array();
    global $_GPC;
    $host_userid     = $_GPC['host_userid'];
    $uniacid        = $_GPC['uniacid'];
    $user_sql   = "select ld.* from live_data ld join ims_mc_members mm on ld.userid = mm.tencentuserid where mm.tencentuserid = '".$host_userid."' and mm.uniacid='".$uniacid."' ";
    $list     =  pdo_fetch($user_sql);
    $record = array();
    if(!empty($list))
    {
            $groupid = '';
            $fileid = '';
            $record_type = 0;
            $desc = '';
            $forbid = '0';
            //get focus
            $focus = 0;
            $vipuser_focus_status = 0;
            $paid_focus = 0;
            $user_id = $list['userid'];
            $focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '".$user_id."' " ;
            $focus_result = pdo_fetch($focus_sql);
            if(!empty($focus_result)) {
                $focus = intval($focus_result['focus_count']);
            }

            $vipuser_focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '".$user_id."' " ;
            $vipuser_focus_result = pdo_fetch($vipuser_focus_sql);
            if(!empty($vipuser_focus_result)) {
                //$vipuser_focus_status = 1;
                $vipuser_focus_status = 0;
            }

            $paid_focus_sql = "select count(*) as focus_count from ims_mc_member_focus fo join ims_mc_members mm on fo.host_userid = mm.tencentuserid  where fo.host_userid = '".$user_id."' and mm.coin > 0 " ;
            $paid_focus_result = pdo_fetch($paid_focus_sql);
            if(!empty($paid_focus_result)) {
                $paid_focus = intval($paid_focus_result['focus_count']);
            }
            $coin = 0;
            $coin_sql = " SELECT sum(coin) FROM ims_mc_member_coin_history WHERE host_userid= '".$user_id."'";
            $coin_result = pdo_fetch($coin_sql);
            if(!empty($coin_result)) {
                $coin = intval($paid_focus_result['coin']);
            }

            $record = array(
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
                'coin'	=> $coin,
                'vipfocusstatus' => $vipuser_focus_status
            );
    }

    $ret['code']    = '200';
    $ret['message'] = 'success';
    $ret['content'] = $record;
    echo json_encode($ret);
    return;
}

if($do == 'getshipaddress') {
    global $_W, $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;

    $ret = array();
    $detail = array();
    $user   = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
    $record = array();
    if(!empty($user)) {
        $uid = $user['uid'];
        $nickname = $user['nickname'];
        $sql = " SELECT *  FROM " . tablename('eso_sale_address') . " WHERE deleted = '0' and openid ='" . $uid . "' and uniacid= '" . $uniacid . "' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
        $list = pdo_fetchall($sql);
        $count = 0;
        foreach ( $list as $li) {
            $list[$count]['nickname'] = $nickname;
            $count++;
        }
        $record = $list;
    }
    $ret['code']    = '200';
    $ret['message'] = 'success';
    $ret['content'] = $record;
    echo json_encode($ret);
    return;
}

if($do == 'getshipaddressinform') {
    global $_W, $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $addressid      = $_GPC['addressid'];

    $ret = array();
    $detail = array();
    $user   = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
    $record = array();
    if(!empty($user)) {
        $uid = $user['uid'];
        $nickname = $user['nickname'];
        $sql = " SELECT *  FROM " . tablename('eso_sale_address') . " WHERE openid ='" . $uid . "' and uniacid= '" . $uniacid . "' and id='".$addressid."'";
        $list = pdo_fetch($sql);
        $list['nickname'] = $nickanme;
        $record = $list;
    }
    $ret['code']    = '200';
    $ret['message'] = 'success';
    $ret['content'] = $record;
    echo json_encode($ret);
    return;
}

if($do == 'addshipaddress') {
    global $_W, $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $addressid      = $_GPC['addressid'];
    $realname       = $_GPC['realname'];
    $mobile         = $_GPC['mobile'];
    $province       = $_GPC['province'];
    $city           = $_GPC['city'];
    $area           = $_GPC['area'];
    $address        = $_GPC['address'];
    $isdefault      = $_GPC['isdefault'];
    $user   = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
    if(!empty($user))
        $uid = $user['uid'];
    else
        $uid = 0;
    if($isdefault == '1') {
        $data = array(
            'isdefault' => '0'
        );
        pdo_update('eso_sale_address', $data, array('openid' => $uid,'uniacid' => $uniacid));
    }
    $data = array(
        'uniacid' => $uniacid,
        'openid' => $uid,
        'realname' => $realname,
        'mobile' => $mobile,
        'province' => $province,
        'city' => $city,
        'area' => $area,
        'address' => $address,
        'isdefault' => $isdefault,
        'deleted' => '0'
    );

    if ($addressid == '0') {
        pdo_insert('eso_sale_address', $data);
    } else {
        pdo_update('eso_sale_address', $data, array('openid' => $uid,'id'=>$addressid));
    }
    $ret = array();
    $ret['code']    = '200';
    $ret['message'] = 'success';
    echo json_encode($ret);
    return;
}

if($do == 'del_ship_address') {
    global $_W, $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];
    $addressid      = $_GPC['addressid'];
    $user   = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
    if(!empty($user))
        $uid = $user['uid'];
    else
        $uid = 0;
    $data = array(
        'deleted' => '1'
    );
    pdo_update('eso_sale_address', $data, array('openid' => $uid,'id'=>$addressid,'uniacid'=>$uniacid));
    $ret = array();
    $ret['code']    = '200';
    $ret['message'] = 'success';
    echo json_encode($ret);
    return;
}

if($do == 'get_ship_address_default') {
    global $_W, $_GPC;
    $vip_userid     = $_GPC['vip_userid'];
    $uniacid        = $_GPC['uniacid'];

    $ret = array();
    $detail = array();
    $user   = pdo_fetch("SELECT * FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $vip_userid,':uniacid'=>$uniacid));
    $record = array();
    if(!empty($user)) {
        $uid = $user['uid'];
        $nickname = $user['nickname'];
        $sql = " SELECT *  FROM " . tablename('eso_sale_address') . " WHERE openid ='" . $uid . "' and uniacid= '" . $uniacid . "' and isdefault='1'";
        $list = pdo_fetch($sql);
        $list['nickname'] = $nickanme;
        $record = $list;
    }
    $ret['code']    = '200';
    $ret['message'] = 'success';
    $ret['content'] = $record;
    echo json_encode($ret);
    return;
}
