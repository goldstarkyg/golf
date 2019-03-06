<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('shop', 'trip', 'home')) ? $do : 'shop';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$public_path = $protocol.$domain;

if($do != 'shop') {
    $ret    = array();
    $pcate  = pdo_fetchall("SELECT id, uniacid, thumb FROM " . tablename('golf_banner') . " where name = '".$do."' ORDER BY displayorder ASC");
    $parent_id = $pcate[0]['id'];
    $ccate  = pdo_fetchall("SELECT id, uniacid, thumb FROM " . tablename('golf_banner') . " where parentid = '".$parent_id."' ORDER BY displayorder ASC");
    $banner = array_merge($pcate,$ccate);
    $i = 0;
    foreach($banner as $ba) {
        $path = $public_path.'/golf/attachment/'.$banner[$i]['thumb'];
        $banner[$i]['thumb'] = $path;
        $i++;
    }
    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $banner;
    echo json_encode($ret);
    return;
}

if($do == 'shop') {
    $ret    = array();
    $banner  = pdo_fetchall("SELECT id, uniacid, xsthumb as thumb FROM ims_eso_sale_goods where isadv= '1' ");
    $i = 0;
    foreach($banner as $ba) {
        $path = $public_path.'/golf/attachment/'.$banner[$i]['thumb'];
        $banner[$i]['thumb'] = $path;
        $i++;
    }
    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $banner;
    echo json_encode($ret);
    return;
}
