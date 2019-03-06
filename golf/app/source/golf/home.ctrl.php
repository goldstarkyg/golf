<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('getfilter',
                            'getrecommendvideo',
                            'getgiftcategory',
                            'getgiftlist',
                            'sendgift',
                            'view_video',
                            'get_video_history',
                            'delete_video_history',
                            'get_fan_card',
                            'buy_fan',
                            'get_fan_group',
                            'get_article_list',
                            'get_article',
                            'get_video_good')) ? $do : 'getfilter';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$public_path = $protocol.$domain;

function getVIPDataList($start_pos, $page_size, &$live_list, $vip_userid, $tagid , $date_val){
    if($tagid != "") {
        $live_base_sql = "select ld.* from live_data ld ";
        $live_base_sql .= " join ims_mc_members mm on ld.userid = mm.tencentuserid ";
        $live_base_sql .= " join video_selected vs on vs.video_id = ld.userid where ld.status = 1 and vs.video_type = 'live' and vs.tag_id = '".$tagid."' ";
    }else {
        $live_base_sql = "select ld.* from live_data ld join ims_mc_members mm on ld.userid = mm.tencentuserid where ld.status = 1 ";
    }
    $live_query_sql     = $live_base_sql . "  order by ld.viewer_count desc limit " . $start_pos . "," . $page_size;
    $result             = pdo_fetchall($live_query_sql);
    //

    if(!empty($result))
    {
        foreach ($result as $list)
        {
            $groupid = '';
            $fileid = '';
            $desc = '';
            $forbid = '0';
            //get focus
            $focus = 0;
            $vipuser_focus_status = 0;
            $paid_focus = 0;
            $host_userid = $list['userid'];
            $focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '".$host_userid."' " ;
            $focus_result = pdo_fetch($focus_sql);
            if(!empty($focus_result)) {
                $focus = intval($focus_result[0]['focus_count']);
            }
            //focus status about vip user
            $vipuser_focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '".$host_userid."' and vip_userid = '".$vip_userid."' " ;
            $vipuser_focus_result = pdo_fetch($vipuser_focus_sql);
            if(!empty($vipuser_focus_result)) {
                $vipuser_focus_status = 1;
            }
            $date = date('Y-m-d');
            $paid_focus_sql = "select count(*) as focus_count from ims_mc_member_focus fo join ims_mc_members mm on fo.host_userid = mm.tencentuserid  ";
            $paid_focus_sql .= " where fo.host_userid = '".$host_userid."' and fo.paid_flag = '1' and fo.end_date >= '".$date."'" ;
            $paid_focus_result = pdo_fetch($paid_focus_sql);
            if(!empty($paid_focus_result)) {
                $paid_focus = intval($paid_focus_result['focus_count']);
            }
            $coin = 0;
            $coin_sql = " SELECT sum(coin) as coin FROM ims_mc_member_coin_history WHERE host_userid= '".$host_userid."'";
            $coin_result = pdo_fetch($coin_sql);
            if(!empty($coin_result)) {
                $coin = intval($coin_result['coin']);
            }
            $groupid = $list['groupid'];
            $desc = $list['desc'];
            $forbid = $list['forbid_status'];

            $order_sql  = " SELECT sum(coin) as coin, host_userid  FROM ims_mc_member_coin_history WHERE created_at >= '".$date_val."' group by host_userid ORDER BY coin DESC";
            $order_result = pdo_fetchall($order_sql);
            $order_count = 0;
            foreach ($order_result as $or) {
                $order_count++;
                if($or['host_userid'] == $host_userid) break;
            }
            $one_record = array(
                'userid'        => $list['userid'],
                'groupid'       =>$groupid,
                'timestamp'     => strtotime($list['create_time']) ? strtotime($list['create_time']) : 0,
                'type'          => 0,
                'viewercount'   => intval($list['viewer_count']),
                'likecount'     => intval($list['like_count']),
                'title'         => $list['title'],
                'playurl'       => $list['play_url'],
                'hls_play_url'  => $list['hls_play_url'],
                'desc'          => $desc,
                'data_type'     => 1,
                'forbid_status' => intval($forbid),
                'status'        => $list['status'],
                'fileid'        => $fileid,
                'userinfo'      => array(
                    'nickname'      => $list['nickname'],
                    'headpic'       => $list['headpic'],
                    'frontcover'    => $list['frontcover'],
                    'location'      => $list['location'],
                ),
                'focuscount'        => $focus,
                'paidfocuscount'    => $paid_focus,
                'coin'	            => $coin,
                'vipfocusstatus'    => $vipuser_focus_status,
                'order'             => $order_count
            );
            array_push($live_list,$one_record);

        }
    }
}

function getUGCList($start_row,$row_number,&$live_list,$vip_userid , $tagid)
{

    if($tagid !="") {
        $search_sql = "select ud.* from UGC_data ud ";
        $search_sql .=" join ims_mc_members mm on ud.userid = mm.tencentuserid " ;
        $search_sql .=" join video_selected as vs on vs.video_id = ud.file_id where vs.video_type='ugc' and vs.tag_id='".$tagid."' and ";
    }else {
        $search_sql = "select ud.* from UGC_data ud join ims_mc_members mm on ud.userid = mm.tencentuserid where ";
    }
    $search_sql .= " ud.userid = '" . $vip_userid . "' order by ud.viewer_count desc limit ". strval($start_row) . "," . strval($row_number);
    $result = pdo_fetchall($search_sql);

        if(!empty($result))
        {
            foreach ($result as $list)
            {
                //get focus
                $focus = 0;
                $paid_focus = 0;
                $user_id = $list['userid'];
                $focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '".$user_id."' " ;
                $focus_result = pdo_fetch($focus_sql);
                if(!empty($focus_result)) {
                    $focus = intval($focus_result['focus_count']);
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

                $one_record = array(
                    'userid' => $list['userid'],
                    'fileid' => $list['file_id'],
                    'timestamp' => strtotime($list['create_time']) ? strtotime($list['create_time']) : 0,
                    'title' => $list['title'],
                    'type' => 2,
                    'data_type' => 1,
                    'playurl' => $list['play_url'],
                    'userinfo' => array(
                        'nickname' => $list['nickname'],
                        'headpic'  => $list['headpic'],
                        'frontcover' => $list['frontcover'],
                        'location' => $list['location'],
                    ),
                    'focuscount' => $focus ,
                    'paidfocuscount' => $paid_focus ,
                    'coin'		 => $coin,
                    'vipfocusstatus' => 0
                );
                array_push($live_list,$one_record);
            }
        }
    }


if($do == 'getfilter') {
    $ret = array();
    $list = array();
    global $_GPC;
    $uniacid           = $_GPC['uniacid'];
    $vip_userid        = $_GPC['vip_userid'];
    //get article list
    $article = pdo_fetchall("SELECT id, title, iscommend, ishot, isnew  FROM " . tablename('golf_article') . " where iscommend='1' ORDER BY click DESC");
    //get video tag list
    $tags = pdo_fetchall("SELECT id, title, ishot , displayorder  FROM video_tag where ishot='1' and isrecommend='1' ORDER BY displayorder DESC");
    //get advertisement
    $adv = pdo_fetchall("SELECT id, name, thumb  FROM " . tablename('golf_adv') . " where isrecommand='1' ORDER BY displayorder DESC");
    $adv_list = array();
    foreach ($adv as $ad) {
        if(!empty($ad['thumb']))
            $ad['thumb'] =  $public_path.'/golf/attachment/'.$ca['thumb'];
        array_push($adv_list,$ad);
    }
    $list['article']    = $article;
    $list['tag']        = $tags;
    $list['adv']        = $adv;
    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $list;
    echo json_encode($ret);
    return;
}
if($do == 'getrecommendvideo') {
    $pcate      = $_GPC['category'];
    $uniacid    = $_GPC['uniacid'];
    $page_size  = $_GPC['page_size'];
    $page_num   = $_GPC['page_num'];
    $vip_userid = $_GPC['vip_userid'];
    $tagid      = $_GPC['tagid'];
    $date       = $_GPC['date'];
    $start_pos = ($page_num -1) * ($page_size);
    $last_flag = 0;
    //get live video

    if($tagid != '') {
        $live_count_sql = "select count(*) as all_count from live_data ld ";
        $live_count_sql .= " join ims_mc_members mm on ld.userid = mm.tencentuserid ";
        $live_count_sql .= " join video_selected vs on vs.video_id = ld.userid where ld.status = 1 and vs.video_type = 'live' and vs.tag_id = '".$tagid."' ";
    }else {
        $live_count_sql = "select count(*) as all_count from live_data ld join ims_mc_members mm on ld.userid = mm.tencentuserid where ld.status = 1 ";
    }
    
    $live_count_result  = pdo_fetch($live_count_sql);
    $live_count         = $live_count_result['all_count'];
    if($tagid !='') {
        $ugc_count_sql = "select count(*) as all_count from UGC_data ug ";
        $ugc_count_sql .=" join ims_mc_members mm on ug.userid = mm.tencentuserid ";
        $ugc_count_sql .=" join video_selected vs on vs.video_id = ug.file_id where vs.video_type = 'ugc' and vs.tag_id='".$tagid."'";
    }else {
        $ugc_count_sql = "select count(*) as all_count from UGC_data ug ";
        $ugc_count_sql .=" join ims_mc_members mm on ug.userid = mm.tencentuserid ";
    }
    $ugc_count_result  = pdo_fetch($ugc_count_sql);
    $ugc_count         = $ugc_count_result['all_count'];
    $result_list = array();
    if($live_count >= ($start_pos+$page_size))
    {
        $ret = getVIPDataList($start_pos, $page_size,$result_list, $vip_userid, $tagid , $date);
        $last_flag = 0;
    }
    elseif($live_count <= $start_pos)
    {
        $start_pos -=  $live_count ;
        $ret = getUGCList( $start_pos, $page_size,$result_list, $vip_userid , $tagid);
        if($ugc_count <= $page_size)
                $last_flag = 1;
        else
            $last_flag = 0;
    }
    else
    {
        $live_list = array();
        $ugc_list = array();
        $tmp_live_num =  $live_count - $start_pos;
        $ret = getVIPDataList($start_pos, $tmp_live_num, $live_list, $vip_userid, $tagid , $date);
        $rest_num = $page_size - $tmp_live_num;
        $ret = getUGCList( 0, $rest_num, $ugc_list, $vip_userid ,$tagid);
        $result_list = array_merge($live_list,$ugc_list);
        $last_flag = 1;
    }
    ///
    $ret['code'] = '200';
    $ret['content']   = $result_list ;
    $ret['last_flag'] = $last_flag ;
    echo json_encode($ret);
    return;
}

if($do == 'getgiftcategory') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $ret = array();
    $category = pdo_fetchall("SELECT id,uniacid,name,displayorder,thumb  FROM gift_category where parentid='0' and enabled = '1' and uniacid='".$uniacid."'  ORDER BY displayorder ASC");
    $list = array();
    foreach ($category as $ca) {
        if(!empty($ca['thumb']))
            $ca['thumb'] =  $public_path.'/golf/attachment/'.$ca['thumb'];
        array_push($list,$ca);
    }

    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $list;
    echo json_encode($ret);
    return;
}

if($do == 'getgiftlist') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $categoryid     = $_GPC['categoryid'];
    $max_size       = $_GPC['maxsize'];
    $ret = array();
    if($max_size > 0 )
        $giftlist = pdo_fetchall("SELECT id, title,description, price, thumb, max_size , stock  FROM gift_list where iscommend='1' and pcate='".$categoryid."' and stock != '0' and max_size='".$max_size."' and uniacid='".$uniacid."'  ORDER BY displayorder ASC");
    else
        $giftlist = pdo_fetchall("SELECT id, title,description, price, thumb, max_size , stock  FROM gift_list where iscommend='1' and pcate='".$categoryid."' and stock != '0' and uniacid='".$uniacid."'  ORDER BY displayorder ASC");
    $list = array();
    foreach ($giftlist as $ca) {
        if(!empty($ca['thumb']))
            $ca['thumb'] =  $public_path.'/golf/attachment/'.$ca['thumb'];
        array_push($list,$ca);
    }

    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $list;
    echo json_encode($ret);
    return;
}

if($do == 'sendgift') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $categoryid     = $_GPC['categoryid'];
    $vip_userid     = $_GPC['vip_userid'];
    $host_userid    = $_GPC['host_userid'];
	$giftid         = $_GPC['giftid'];
    $gift           = pdo_fetch("SELECT * FROM gift_list where id='".$giftid."'");
    $price          = $gift['price'];
    $stock          = $gift['stock'];
    $gift_history   = pdo_fetch("SELECT * FROM gift_history where vip_userid='".$vip_userid."' and host_userid ='".$host_userid."' and uniacid='".$uniacid."' ");
    if(!empty($stock) && $stock > 0) {
        $data = array(
            'uniacid' => $uniacid,
            'vip_userid' => $vip_userid,
            'host_userid' => $host_userid,
            'category_id' => $categoryid,
            'gift_id' => $giftid
        );
        pdo_insert_table('gift_history', $data);
        //coin history to get coin ranking
        $data_coin = array(
            'coin'          => $price,
            'vip_userid'    => $vip_userid,
            'host_userid'   => $host_userid,
            'uniacid'       => $uniacid,
            'type'          => 'gift',
            'option_type'   => 'out'
        );
        pdo_insert_table('ims_mc_member_coin_history', $data_coin);

        //minus price to vip user
        $vip = pdo_fetch("SELECT * FROM ims_mc_members where tencentuserid ='" . $vip_userid . "'");
        $vip_price = $vip['coin'] - $price;
        $vip_data = array('coin' => $vip_price);
        pdo_update_table('ims_mc_members', $vip_data, array('tencentuserid' => $vip_userid));
        //add price host user
        $host = pdo_fetch("SELECT * FROM ims_mc_members where tencentuserid ='" . $host_userid . "'");
        $host_price = $hot['coin'] + $price;
        $host_data = array('coin' => $host_price);
        pdo_update_table('ims_mc_members', $host_data, array('tencentuserid' => $host_userid));
        //update gift stock
        $stock = $stock -1 ;
        $data_stock = array('stock' => $stock);
        pdo_update_table('gift_list', $data_stock, array('id' => $giftid));
        $ret = array();
        $list = array();
        $ret['code'] = '200';
        $ret['content'] = $list;
        echo json_encode($ret);
    }else {
        $ret['code'] = '403';
        $ret['message'] = " There is no stock for this gift!";
        echo json_encode($ret);
    }
    return;
}

if($do == 'view_video') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $vip_userid     = $_GPC['vip_userid'];
    $video_type     = $_GPC['video_type'];
    $video_id       = $_GPC['video_id'];
    $video_his_sql = "select * from video_history where vip_userid = '" . $vip_userid . "' and video_type='live' and video_id='".$video_id."'";
    $result = pdo_fetch($video_his_sql);
    if(empty($result)) {
        $data = array(
            'uniacid'       => $uniacid,
            'vip_userid'    => $vip_userid,
            'video_type'    => $video_type,
            'video_id'      => $video_id
        );
        pdo_insert_table('video_history', $data);
    }else {
        $data = array('created_at'    => date('Y-m-d H:i:s'));
        pdo_update_table('video_history', $data, array('vip_userid' => $vip_userid, 'video_type'=> $video_type , 'video_id'=>$video_id));
    }
}

if($do == 'get_video_history') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $vip_userid     = $_GPC['vip_userid'];
    $video_type     = $_GPC['video_type'];
    $page_num       = $_GPC['page_num'];
    $page_size  = 10;
    $start_pos = ($page_num -1) * ($page_size);
    $ret = array();
    $video_list = array();
    if($video_type == 'live') {
        $video_his_sql = "select ld.*, vh.created_at from video_history vh join live_data ld on vh.video_id = ld.userid where vh.vip_userid = '" . $vip_userid . "' and vh.video_type='live' ";
        $video_his_sql .= "  group by vh.video_id order by vh.created_at desc limit " . $start_pos . "," . $page_size;
        $result = pdo_fetchall($video_his_sql);

        if (!empty($result)) {
            foreach ($result as $list) {
                $groupid = '';
                $fileid = '';
                $desc = '';
                $forbid = '0';
                //get focus
                $focus = 0;
                $vipuser_focus_status = 0;
                $paid_focus = 0;
                $host_userid = $list['userid'];
                $focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '" . $host_userid . "' ";
                $focus_result = pdo_fetch($focus_sql);
                if (!empty($focus_result)) {
                    $focus = intval($focus_result[0]['focus_count']);
                }
                //focus status about vip user
                $vipuser_focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '" . $host_userid . "' and vip_userid = '" . $vip_userid . "' ";
                $vipuser_focus_result = pdo_fetch($vipuser_focus_sql);
                if (!empty($vipuser_focus_result)) {
                    $vipuser_focus_status = 1;
                }
                $date = date('Y-m-d');
                $paid_focus_sql = "select count(*) as focus_count from ims_mc_member_focus fo join ims_mc_members mm on fo.host_userid = mm.tencentuserid  ";
                $paid_focus_sql .= " where fo.host_userid = '" . $host_userid . "' and fo.paid_flag = '1' and fo.end_date >= '" . $date . "'";
                $paid_focus_result = pdo_fetch($paid_focus_sql);
                if (!empty($paid_focus_result)) {
                    $paid_focus = intval($paid_focus_result['focus_count']);
                }
                $coin = 0;
                $coin_sql = " SELECT sum(coin) as coin FROM ims_mc_member_coin_history WHERE host_userid= '" . $host_userid . "'";
                $coin_result = pdo_fetch($coin_sql);
                if (!empty($coin_result)) {
                    $coin = intval($coin_result['coin']);
                }
                $groupid = $list['groupid'];
                $desc = $list['desc'];
                $forbid = $list['forbid_status'];

                $cu_datetime = strtotime(date('Y-m-d H:i:s'));
                $origin_datetime= strtotime($list['created_at']);
                $view_time = intval($cu_datetime) - intval($origin_datetime);
                $one_record = array(
                    'userid' => $list['userid'],
                    'groupid' => $groupid,
                    'timestamp' => strtotime($list['create_time']) ? strtotime($list['create_time']) : 0,
                    'type' => 0,
                    'viewercount' => intval($list['viewer_count']),
                    'likecount' => intval($list['like_count']),
                    'title' => $list['title'],
                    'playurl' => $list['play_url'],
                    'hls_play_url' => $list['hls_play_url'],
                    'desc' => $desc,
                    'data_type' => 1,
                    'forbid_status' => intval($forbid),
                    'status' => $list['status'],
                    'fileid' => $fileid,
                    'userinfo' => array(
                        'nickname' => $list['nickname'],
                        'headpic' => $list['headpic'],
                        'frontcover' => $list['frontcover'],
                        'location' => $list['location'],
                    ),
                    'focuscount' => $focus,
                    'paidfocuscount' => $paid_focus,
                    'coin' => $coin,
                    'vipfocusstatus' => $vipuser_focus_status,
                    'view_time' => (string)$view_time
                );
                array_push($video_list, $one_record);
            }
        }
    }
    if($video_type == 'ugc') {
        $video_his_sql = "select ud.* from video_history vh join UGC_data ud on vh.video_id = ud.file_id where vh.vip_userid = '" . $vip_userid . "' and vh.video_type='ugc' ";
        $video_his_sql .= "  group by vh.video_id order by vh.created_at desc limit " . $start_pos . "," . $page_size;
        $result = pdo_fetchall($video_his_sql);
        if(!empty($result))
        {
            foreach ($result as $list)
            {
                //get focus
                $focus = 0;
                $paid_focus = 0;
                $user_id = $list['userid'];
                $focus_sql = "select count(*) as focus_count from ims_mc_member_focus where host_userid = '".$user_id."' " ;
                $focus_result = pdo_fetch($focus_sql);
                if(!empty($focus_result)) {
                    $focus = intval($focus_result['focus_count']);
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
                $cu_datetime = strtotime(date('Y-m-d H;i:s'));
                $origin_datetime= strtotime($list['created_at']);
                $view_time = $cu_datetime - $origin_datetime;

                $one_record = array(
                    'userid' => $list['userid'],
                    'fileid' => $list['file_id'],
                    'timestamp' => strtotime($list['create_time']) ? strtotime($list['create_time']) : 0,
                    'title' => $list['title'],
                    'type' => 2,
                    'data_type' => 1,
                    'playurl' => $list['play_url'],
                    'userinfo' => array(
                        'nickname' => $list['nickname'],
                        'headpic'  => $list['headpic'],
                        'frontcover' => $list['frontcover'],
                        'location' => $list['location'],
                    ),
                    'focuscount' => $focus ,
                    'paidfocuscount' => $paid_focus ,
                    'coin'		 => $coin,
                    'vipfocusstatus' => 0,
                    'view_time' => (string)$view_time
                );
                array_push($video_list,$one_record);
            }
        }
    }
    $ret['code'] = '200';
    $ret['content'] = $video_list;
    echo json_encode($ret);
}

if($do == 'delete_video_history') {
    global $_GPC;
    $uniacid = $_GPC['uniacid'];
    $vip_userid = $_GPC['vip_userid'];
    $vip_userid = $_GPC['vip_userid'];
    pdo_delete_table('video_history', array('vip_userid' => $vip_userid));
    $ret = array();
    $ret['code'] = '200';
    $ret['content'] = [];
    echo json_encode($ret);
}

if($do == 'get_fan_card') {
    global $_GPC;
    $uniacid           = $_GPC['uniacid'];
    $vip_userid        = $_GPC['vip_userid'];
    $host_userid       = $_GPC['host_userid'];
    //get fan list
    $fanlist = pdo_fetchall("SELECT *  FROM fan_list where iscommend='1' ORDER BY displayorder ASC");
    $fan_inform = pdo_fetch("SELECT *  FROM ims_mc_member_focus where vip_userid='".$vip_userid."' and host_userid='".$host_userid."' ");;
    $final_date = $fan_inform['end_date'];
    $period = 0;
    if(!empty($fan_inform))
        $period = $fan_inform['period'];
    $fan_result = array();
    foreach ($fanlist as $list) {
        if($period == $list['period']) $list['buy'] = '1';
        else $list['buy'] = '0';
        array_push($fan_result, $list);
    }
    $current_date = date('Y-m-d');
    $status = '0';
    if(strtotime($final_date) > strtotime($current_date )) $status = '1';
    $result = array();
    $result['card']     = $fan_result;
    $result['status']   = $status;
    $ret = array();
    $ret['code'] = '200';
    $ret['content'] = $result;
    echo json_encode($ret);
}

if($do == 'buy_fan') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $period         = $_GPC['period'];
    $vip_userid     = $_GPC['vip_userid'];
    $host_userid   = $_GPC['host_userid'];
    $current_date    = date("Y-m-d");
    $final = date("Y-m-d", strtotime("+".$period." month", strtotime($current_date)));
    $fan = pdo_fetch("SELECT *  FROM ims_mc_member_focus where vip_userid='".$vip_userid."' and host_userid='".$host_userid."' ");
    $fan_data = array("paid_flag"       =>  "1",
                        "period"        =>  $period,
                        'start_date'    =>  $current_date,
                        "end_date"      =>  $final,
                        "host_userid"   =>  $host_userid,
                        "vip_userid"    =>  $vip_userid);
    if(empty($fan)) {
        pdo_insert_table('ims_mc_member_focus', $fan_data );
    }else{
        pdo_update_table('ims_mc_member_focus', $fan_data, array("host_userid" => $host_userid,"vip_userid"=> $vip_userid));
    }
    ///////save history
    $pricedetail = pdo_fetch("SELECT *  FROM fan_list where period='".$period."'");
    $price = $pricedetail['price'];
    $data_coin = array(
        'coin'          => $price,
        'vip_userid'    => $vip_userid,
        'host_userid'   => $host_userid,
        'uniacid'       => $uniacid,
        'type'          => 'fan',
        'option_type'   => 'out'
    );
    pdo_insert_table('ims_mc_member_coin_history', $data_coin);
    /////////
    //get fan list
    $fanlist = pdo_fetchall("SELECT *  FROM fan_list where iscommend='1' ORDER BY displayorder ASC");
    $fan_inform = pdo_fetch("SELECT *  FROM ims_mc_member_focus where vip_userid='".$vip_userid."' and host_userid='".$host_userid."' ");;
    $final_date = $fan_inform['end_date'];
    $period = 0;
    if(!empty($fan_inform))
        $period = $fan_inform['period'];
    $fan_result = array();
    foreach ($fanlist as $list) {
        if($period == $list['period']) $list['buy'] = '1';
        else $list['buy'] = '0';
        array_push($fan_result, $list);
    }
    $status = '0';
    if(strtotime($final_date) > strtotime($current_date )) $status = '1';
    $result = array();
    $result['card']     = $fan_result;
    $result['status']   = $status;
    $ret = array();
    $ret['code'] = '200';
    $ret['content'] = $result;
    echo json_encode($ret);
}

if($do == 'get_fan_group') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $host_userid   = $_GPC['host_userid'];
    $current_date    = date("Y-m-d");
    $paid_focus_sql = "select mm.tencentuserid as userid, mm.avatar as thumb , mm.nickname, mm.gender from ims_mc_member_focus fo left join ims_mc_members mm on fo.host_userid = mm.tencentuserid  where fo.host_userid = '".$host_userid."' " ;
    $paid_focus_sql .=" and  fo.period='1' and fo.end_date >= '".$current_date."' ";
    $paid_focus_list = pdo_fetchall($paid_focus_sql);
    $result = array();
    foreach ($paid_focus_list as $li) {
        if(!empty($li['thumb']))
            $li['thumb'] =  $public_path.'/golf/attachment/'.$li['thumb'];
        if($li['gender'] == '1' || $li['gender'] == '0')
            $li['gender'] =  '男';
        if($li['gender'] == '2')
            $li['gender'] =  '女';
        array_push($result,$li);
    }
    $ret = array();
    $ret['code'] = '200';
    $ret['content'] = $result;
    echo json_encode($ret);
}

if($do == 'get_article_list') {
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $pindex         = intval($_GPC['page_num']);
    $pcate          = $_GPC['pcate'];
    $psize = 20;
    if($pcate != 0 )
        $sql = "SELECT id, description, title, thumb , click FROM ims_golf_article WHERE uniacid = '".$uniacid."' and pcate='".$pcate."' ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize ;
    else
        $sql = "SELECT id, description, title, thumb , click FROM ims_golf_article WHERE uniacid = '".$uniacid."'  ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize ;
    $list = pdo_fetchall($sql);
    $result = array();
    foreach ( $list as $li)
    {
        if(!empty($li['thumb']))
            $li['thumb'] =  $public_path.'/golf/attachment/'.$li['thumb'];
        array_push($result,$li);
    }

    $ret = array();
    $ret['code'] = '200';
    $ret['content'] = $result;
    echo json_encode($ret);
}

if($do == 'get_article'){
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $articel_id     = $_GPC['article_id'];
    $view_sql = "SELECT click FROM ims_golf_article WHERE uniacid = '".$uniacid."' and id='".$articel_id."'" ;
    $view_list = pdo_fetch($view_sql);
    $count = $view_list['click'];
    //update count
    $data = array('click' => $count+1);
    pdo_update_table('ims_golf_article', $data, array('uniacid' => $uniacid, 'id' => $articel_id));

    $sql = "SELECT id, description, content, title, thumb , click FROM ims_golf_article WHERE uniacid = '".$uniacid."' and id='".$articel_id."'" ;
    $list = pdo_fetch($sql);
    if(!empty($list['thumb']))
            $list['thumb'] =  $public_path.'/golf/attachment/'.$list['thumb'];
    $ret = array();
    $ret['code'] = '200';
    $ret['content'] = $list;
    echo json_encode($ret);
}

if($do == 'get_video_good'){
    global $_GPC;
    $uniacid        = $_GPC['uniacid'];
    $host_userid    = $_GPC['host_userid'];
    $page_num       = $_GPC['page_num'];
    $page_size      = 20;
    $start_pos = ($page_num -1) * ($page_size);

    $member_sql = "SELECT pcate FROM ims_mc_members WHERE uniacid = '".$uniacid."' and tencentuserid='".$host_userid."'" ;
    $member = pdo_fetch($member_sql);
    $pcate  = $member['pcate'];
    $goods_sql = "select g.* from video_good_list vg left join ims_eso_sale_goods g on vg.good_id = g.id  where vg.category_id = '".$pcate."' ";
    $goods_sql .=" and g.uniacid='".$uniacid."' limit ". $start_pos . "," . $page_size ;
    $list = pdo_fetchall($goods_sql);
    $result =array();
    foreach ($list as $li){
        $li['thumb'] = $public_path.'/golf/attachment/'.$li['thumb'];
        $li['xsthumb'] = $public_path.'/golf/attachment/'.$li['xsthumb'];
        array_push($result,$li);
    }
    $ret = array();
    $ret['code'] = '200';
    $ret['content'] = $result;
    echo json_encode($ret);
}

