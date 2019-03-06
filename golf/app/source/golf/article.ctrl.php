<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('list', 'detail', 'favorite')) ? $do : 'list';

global $_W, $_GPC;
$category = pdo_fetchall("SELECT id,parentid,name FROM ".tablename('golf_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
$parent = array();
$children = array();

if (!empty($category)) {
    $children = '';
    foreach ($category as $cid => $cate) {
        if (!empty($cate['parentid'])) {
            $children[$cate['parentid']][] = $cate;
        } else {
            $parent[$cate['id']] = $cate;
        }
    }
}
if($do == 'list') {
    load()->app('golf');
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = '';
    $params = array();
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['category']['childid'])) {
        $cid = intval($_GPC['category']['childid']);
        $condition .= " AND ccate = '{$cid}'";
    } elseif (!empty($_GPC['category']['parentid'])) {
        $cid = intval($_GPC['category']['parentid']);
        $condition .= " AND pcate = '{$cid}'";
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename('golf_article') . " WHERE uniacid != '0'  $condition ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('golf_article') . " WHERE uniacid != '0' ");
    $pager = pagination($total, $pindex, $psize);
    $i = 0;
    $data = array();
    $data =  $list;
    foreach ($list as $li) {
        mb_internal_encoding("UTF-8");
        if(mb_strlen($li['title']) > 10) $list[$i]['title'] = mb_substr($li['title'], 0 , 10);
        if(mb_strlen($li['description']) > 10) $list[$i]['description'] = mb_substr($li['description'], 0 , 10);
        $i++;
    }
    $ret = array();
    $ret['code']    = '200';
    $ret['messsage']= '';
    $ret['content'] = $list;
    //$ret['total']   = $total;
    //$ret['pager']   = $pager;
    echo json_encode($ret);
    return;
}elseif($do == 'detail') {
    load()->app('golf');
    $cu_token   = compareToken();
    $aid = $_GPC['aid'];
    $detail = array();
    if($cu_token == 'false') {
        $detail = pdo_fetch("SELECT * FROM " . tablename('golf_article') . " WHERE id = '" . $aid . "'");
        $detail['favorite'] = '0';
    }else{
        $mid = $_GPC['mid'];
        $sql  = "select a.*,f.id as favorite from " . tablename('golf_article') . " as a ";
        $sql .=" left join " . tablename('golf_favorite') . " as f on a.id = f.aid where f.mid='".$mid."'";
        $detail = pdo_fetch($sql);
        if(empty($detail)) {
            $detail = pdo_fetch("SELECT * FROM " . tablename('golf_article') . " WHERE id = '" . $aid . "'");
            $detail['favorite'] = '0';
        }
    }


    $detail['click'] = intval($detail['click']) + 1;
    pdo_update('golf_article', array('click' => $detail['click']), array('id' => $id));

    $ret = array();
    if (!empty($detail)) {
        $ret['code'] = '200';
        $ret['messsage'] = 'sucess!';
        $ret['content'] = $detail;
        echo json_encode($ret);
        return;
    } else {
        $ret['code'] = '301';
        $ret['messsage'] = 'This is empty';
        $ret['content'] = array();
        echo json_encode($ret);
        return;
    }
}elseif($do == 'favorite') {
    load()->app('golf');
    $cu_token   = compareToken();
    if($cu_token == 'false') {
        $ret['code']        = '303';
        $ret['messsage']    = 'no login!';
        $ret['content']     = array();
        echo json_encode($ret);
        return;
    }else {
        $aid = $_GPC['aid'];
        $mid = $_GPC['mid'];
        $favorite = pdo_fetch("SELECT * FROM " . tablename('golf_favorite') . " WHERE aid = '" . $aid . "' and mid='".$mid."'");
        $data = array();

        if(empty($favorite)) {
            $data['mid']  = $mid;
            $data['aid']  = $aid;
            pdo_insert('golf_favorite', $data);
        }
        $sql  = "select a.*,f.id as favorite from " . tablename('golf_article') . " as a ";
        $sql .=" left join " . tablename('golf_favorite') . " as f on a.id = f.aid where f.mid='".$mid."'";
        $detail = pdo_fetch($sql);
        if(!empty($detail)) {
            $ret['code']        = '200';
            $ret['messsage']    = 'success';
            $ret['content']     = $detail;
            echo json_encode($ret);
            return;
        }else {
            $ret['code']        = '304';
            $ret['messsage']    = 'empty!';
            $ret['content']     = array();
            echo json_encode($ret);
            return;
        }
    }
}