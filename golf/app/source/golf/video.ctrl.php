<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('gethostinform','sendvideocomment','getvideocomment')) ? $do : 'gethostinform';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$public_path = $protocol.$domain;

if($do == 'gethostinform') {
    $result = array();
    $host_userid      = $_GPC['host_userid'];
    $uniacid    = $_GPC['uniacid'];
    //1. get the current number of viewers
    $live_data = pdo_fetch("SELECT * FROM live_data where userid = '".$host_userid."' ");
    $viewer = '0';
    if(empty($live_data['viewer_count'])) $viewer = '0';
    else $viewer = $live_data['viewer_count'];
    $result['viewer'] = $viewer;
    //2 padi focus number
    $date = date('Y-m-d');
    $paid_focus_sql = "select count(*) as focus_count from ims_mc_member_focus fo join ims_mc_members mm on fo.host_userid = mm.tencentuserid  ";
    $paid_focus_sql .= " where fo.host_userid = '".$host_userid."' and fo.paid_flag = '1' and fo.end_date >= '".$date."'" ;
    $paid_focus_result = pdo_fetch($paid_focus_sql);
    if(empty($paid_focus_result)) $paid_fan_count = '0';
    else $paid_fan_count = $paid_focus_result['focus_count'];
    $result['paidfan'] = $paid_fan_count ;
    //3 number of fans
    $fan = pdo_fetch(" SELECT count(*) fan_count  FROM ims_mc_member_focus WHERE host_userid = '" . $host_userid . "'");
    $fan_number = $fan['fan_count'];
    if(empty($fan_number)) $fan_number = '0';
    $result['fan'] = $fan_number;
    //4 get notice
    $user = pdo_fetch(" SELECT  nickname,notice, dynamic FROM ims_mc_members WHERE uniacid='".$uniacid."' and tencentuserid = '" . $host_userid . "'");
    $notice         = '';
    if(empty($user['notice'])) $notice = '';
    else $notice = $user['notice'];
    $result['notice'] = $notice;
    //5. anchor head image
    $headpic = '';
    if(empty($live_data['headpic'])) $headpic = '';
    else $headpic = $live_data['headpic'];
    $result['headpic'] = $headpic;
    //6, get name of host user
    $name = '';
    if(empty($user['nickname'])) $name = '';
    else $name = $user['nickname'];
    $result['name'] = $name;
    //7, dynamic content
    $dynamic = "";
    if(empty($user['dynamic'])) $dynamic = '';
    else $dynamic = $user['dynamic'];
    $result['dynamic'] = $dynamic;
    //8, comment of users
    $comment = pdo_fetchall(" SELECT *  FROM video_comment WHERE uniacid='".$uniacid."' and host_userid='".$host_userid."' order by created_at desc limit 0, 10 ");
    $result['comment'] = $comment;
    //$result['commentcount'] = count($comment);
    //get live video

    $ret = array();
    $ret['code'] = '200';
    $ret['content']   = $result ;
    echo json_encode($ret);
    return;
}

if($do == 'sendvideocomment') {
    $result = array();
    $host_userid     = $_GPC['host_userid'];
    $vip_userid      = $_GPC['vip_userid'];
    $vip_username        = $_GPC['vip_username'];
    $comment         = $_GPC['comment'];
    $uniacid    = $_GPC['uniacid'];
    $data = array(
        'uniacid'      => $uniacid,
        'host_userid'  => $host_userid,
        'vip_userid'   => $vip_userid,
        'vip_username' => $vip_username,
        'comment'      => $comment
    );
    pdo_insert_table('video_comment', $data);
    $video_list = pdo_fetchall(" SELECT *  FROM video_comment WHERE uniacid='".$uniacid."'  and host_userid='".$host_userid."' order by created_at desc limit 0, 10 ");
    $ret = array();
    $ret['code'] = '200';
    $ret['content']   = $video_list ;
    echo json_encode($ret);
    return;
}

if($do == 'getvideocomment') {
    $uniacid    = $_GPC['uniacid'];
    $page_size  = $_GPC['page_size'];
    $page_size  = 10;
    $page_num   = $_GPC['page_num'];
    $vip_userid = $_GPC['vip_userid'];
    $host_userid = $_GPC['host_userid'];
    $start_pos = ($page_num -1) * ($page_size);

    $sql    = "select * from video_comment where host_userid = '".$host_userid."' and uniacid = '".$uniacid."' order by created_at desc limit " . $start_pos . "," . $page_size;
    $result = pdo_fetchall($sql);
    ///
    $ret['code'] = '200';
    $ret['content']   = $result ;
    echo json_encode($ret);
    return;
}




