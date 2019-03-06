<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('list', 'detail', 'handsel')) ? $do : 'list';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$public_path = $protocol.$domain;

if($do == 'list') {
    global $_GPC;
    $vip_userid        = $_GPC['vip_userid'];
    $ret = array();
    $category = pdo_fetchall("SELECT id,uniacid,name,displayorder,thumb,type , type_value  FROM " . tablename('golf_category') . " where parentid='0' and enabled = '1'  ORDER BY displayorder ASC");
    $list = array();
    foreach ($category as $ca) {
        if(!empty($ca['thumb']))
            $ca['thumb'] =  $public_path.'/golf/attachment/'.$ca['thumb'];
        array_push($list,$ca);
    }

    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $list;
    //header('Content-type: application/json;charset=utf-8');
    echo json_encode($ret);
    return;
}
