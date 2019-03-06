<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
//set order stock
function setOrderStock($id = '', $minus = true)
{

    $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,g.total as goodstotal,o.total,o.optionid,g.sales FROM " . tablename('eso_sale_order_goods') . " o left join " . tablename('eso_sale_goods') . " g on o.goodsid=g.id "
        . " WHERE o.orderid='{$id}'");
    foreach ($goods as $item) {
        if ($minus) {
            //属性
            if (!empty($item['optionid'])) {
                pdo_query("update " . tablename('eso_sale_goods_option') . " set stock=stock-:stock where id=:id", array(":stock" => $item['total'], ":id" => $item['optionid']));
            }
            $data = array();
            if (!empty($item['goodstotal']) && $item['goodstotal'] != -1) {
                $data['total'] = $item['goodstotal'] - $item['total'];
            }
            $data['sales'] = $item['sales'] + $item['total'];
            pdo_update('eso_sale_goods', $data, array('id' => $item['id']));
        } else {
            //属性
            if (!empty($item['optionid'])) {
                pdo_query("update " . tablename('eso_sale_goods_option') . " set stock=stock+:stock where id=:id", array(":stock" => $item['total'], ":id" => $item['optionid']));
            }
            $data = array();
            if (!empty($item['goodstotal']) && $item['goodstotal'] != -1) {
                $data['total'] = $item['goodstotal'] + $item['total'];
            }
            $data['sales'] = $item['sales'] - $item['total'];
            pdo_update('eso_sale_goods', $data, array('id' => $item['id']));
        }
    }
}

//设置订单积分
function setOrderCredit($orderid, $add = true)
{
    global $_W, $_GPC;
    $uid = $_GPC['uid'];
    $order = pdo_fetch("SELECT * FROM " . tablename('eso_sale_order') . " WHERE id = :id limit 1", array(':id' => $orderid));
    if (empty($order)) {
        return false;
    }
    $sql = 'SELECT `goodsid`, `total` FROM ' . tablename('eso_sale_order_goods') . ' WHERE `orderid` = :orderid';
    $orderGoods = pdo_fetchall($sql, array(':orderid' => $orderid));
    if (!empty($orderGoods)) {
        $credit = 0;
        $sql = 'SELECT `credit` FROM ' . tablename('eso_sale_goods') . ' WHERE `id` = :id';
        foreach ($orderGoods as $goods) {
            $goodsCredit = pdo_fetchcolumn($sql, array(':id' => $goods['goodsid']));
            $credit += $goodsCredit * $goods['total'];
        }
    }
    //增加积分
    if (!empty($credit)) {
        load()->model('mc');
        load()->func('compat.biz');
        $uid = mc_openid2uid($order['from_user']);
        $fans = fans_search($uid, array("credit1"));
        if (!empty($fans)) {
            if (!empty($add)) {
                mc_credit_update($uid, 'credit1', $credit, array('0' => $uid, '购买商品赠送'));
            } else {
                mc_credit_update($uid, 'credit1', 0 - $credit, array('0' => $uid, '微商城操作'));
            }
        }
    }
}
//pay result
function payResult($params)
{
    global $_W , $_GPC;
    $_W['uniacid'] = $_GPC['uniacid'];
    $fee = intval($params['fee']);
    $data = array('status' => $params['result'] == 'success' ? 1 : 0);
    $paytype = array('credit' => '1', 'wechat' => '2', 'alipay' => '2', 'delivery' => '3');
    $data['paytype'] = $paytype[$params['type']];
    if ($params['type'] == 'wechat') {
        $data['transid'] = $params['tag']['transaction_id'];
    }
    if ($params['type'] == 'delivery') {
        $data['status'] = 1;
    }
    $sql = 'SELECT `goodsid` FROM ' . tablename('eso_sale_order_goods') . ' WHERE `orderid` = :orderid';
    $goodsId = pdo_fetchcolumn($sql, array(':orderid' => $params['tid']));
    $sql = 'SELECT `total`, `totalcnf` FROM ' . tablename('eso_sale_goods') . ' WHERE `id` = :id';
    $goodsInfo = pdo_fetch($sql, array(':id' => $goodsId));
    // 更改库存
    if ($goodsInfo['totalcnf'] == '1' && !empty($goodsInfo['total'])) {
        pdo_update('eso_sale_goods', array('total' => $goodsInfo['total'] - 1), array('id' => $goodsId));
    }
    pdo_update('eso_sale_order', $data, array('id' => $params['tid']));
    if ($params['from'] == 'return') {
        //积分变更
        setOrderCredit($params['tid']);
        //邮件提醒
        /*
        if (!empty($this->module['config']['noticeemail'])) {
            $order = pdo_fetch("SELECT `price`, `paytype`, `from_user`, `addressid` FROM " . tablename('eso_sale_order') . " WHERE id = '{$params['tid']}'");
            $ordergoods = pdo_fetchall("SELECT goodsid, total FROM " . tablename('eso_sale_order_goods') . " WHERE orderid = '{$params['tid']}'", array(), 'goodsid');
            $goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total FROM " . tablename('eso_sale_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
            $address = pdo_fetch("SELECT * FROM " . tablename('eso_sale_address') . " WHERE id = :id", array(':id' => $order['addressid']));
            $body = "<h3>购买商品清单</h3> <br />";
            if (!empty($goods)) {
                foreach ($goods as $row) {
                    $body .= "名称：{$row['title']} ，数量：{$ordergoods[$row['id']]['total']} <br />";
                }
            }
            $paytype = $order['paytype'] == '3' ? '货到付款' : '已付款';
            $body .= "<br />总金额：{$order['price']}元 （{$paytype}）<br />";
            $body .= "<h3>购买用户详情</h3> <br />";
            $body .= "真实姓名：{$address['realname']} <br />";
            $body .= "地区：{$address['province']} - {$address['city']} - {$address['area']}<br />";
            $body .= "详细地址：{$address['address']} <br />";
            $body .= "手机：{$address['mobile']} <br />";
            load()->func('communication');
            ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
        }*/
        $setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
        $credit = $setting['creditbehaviors']['currency'];

        if ($params['type'] == $credit) {
            //message('支付成功！', $this->createMobileUrl('myorder'), 'success');
        } else {
            //message('支付成功！', '../../app/' . $this->createMobileUrl('myorder'), 'success');
        }
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = '支付成功！';//payment success
        echo json_encode($ret);
        return;
    }
}

//get cart total
function getCartTotal()
{
    global $_W, $_GPC;
    $_W['uniacid']  = $_GPC['uniacid'];
    $from_user      = $_GPC['uid'];
    $cartotal = pdo_fetchcolumn("select sum(total) from " . tablename('eso_sale_cart') . " where uniacid = '{$_W['uniacid']}' and from_user='" . $from_user . "'");
    return empty($cartotal) ? 0 : $cartotal;
}

require_once IA_ROOT.'/payment/wechat/wxpay/WxPay.Api.php';
require_once IA_ROOT.'/payment/wechat/wxpay/log.php';


defined('IN_IA') or exit('Access Denied');
$do = in_array($do, array('hot_category', 'subcategory', 'list', 'detail','cart','order','pay','myorder','shop_week_recommend','shop_adv_good')) ? $do : 'list';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$public_path = $protocol.$domain;


if($do == 'hot_category') {
    global $_GPC, $_W;
    $uniacid = $_GPC['uniacid'];
    //$uniacid = 8;
    $ret = array();
    $category = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_category') . " where ishot='1' and uniacid='8' and parentid='0' ORDER BY displayorder ASC");
    $i = 0;
    foreach($category as $ca) {
        $path = $public_path.'/golf/attachment/'.$category[$i]['thumb'];
        $category[$i]['thumb'] = $path;
        $i++;
    }
    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $category;
    echo json_encode($ret);
    return;
}

if($do == 'subcategory') {
    global $_GPC, $_W;
    $uniacid = $_GPC['uniacid'];
    //$uniacid = 8;
    $pcate = $_GPC['pcate'];
    $ret = array();
    $category = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_category') . " where uniacid='8' and parentid='".$pcate."' ORDER BY displayorder ASC");
    $i = 0;
    foreach($category as $ca) {
        $path = $public_path.'/golf/attachment/'.$category[$i]['thumb'];
        $category[$i]['thumb'] = $path;
        $i++;
    }
    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $category;
    echo json_encode($ret);
    return;
}

if($do == 'list') {
    global $_GPC, $_W;
    $_W['uniacid'] = $_GPC['uniacid']; //8
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20; //number per every page
    $condition = '';
    if (!empty($_GPC['pcate'])) {
        $pcate = intval($_GPC['pcate']);
        if($pcate != 0)
            $condition .= " AND pcate = '".$pcate."'";

    }
    if (!empty($_GPC['ccate'])) {
        $ccate = intval($_GPC['ccate']);
        if($ccate != 0)
            $condition .= " AND ccate = '".$ccate."'";
    }
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
    }
    //首页推荐
    $condition .= ' and isrecommand=1 ';
    $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_goods') . " WHERE uniacid = '{$_W['uniacid']}'  and deleted=0 AND status = '1' $condition ORDER BY displayorder DESC, sales DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total= pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('eso_sale_goods') . " WHERE uniacid = '{$_W['uniacid']}'  and deleted=0  AND status = '1' $condition  ");
    $i = 0;
    foreach($list as $li) {
        $path = $public_path.'/golf/attachment/'.$list[$i]['thumb'];
        $list[$i]['thumb'] = $path;
        $i++;
    }
    $ret = array();
    $data = array();
    $data['list']   = $list;
    $data['total']  = $total;
    $ret['code']    = '200';
    $ret['message'] = '';
    $ret['content'] = $data;
    echo json_encode($ret);
    return;
}

if($do == 'detail') {
    global $_GPC, $_W;
    $uniacid = $_GPC['uniacid'];//8
    $id = intval($_GPC['goodid']);
    //首页推荐
    $condition = ' and isrecommand=1 AND id='.$id;
    $detail = pdo_fetch("SELECT * FROM " . tablename('eso_sale_goods') . " WHERE uniacid = '".$uniacid."'  and deleted=0 AND status = '1' $condition ORDER BY displayorder DESC, sales DESC");
    if(!empty($detail)){
        $detail['thumb'] = $public_path.'/golf/attachment/'.$detail['thumb'];

        $detail['property_1']['name'] = $detail['property1'];
        $goods_id = $detail['id'];
        $property_sql =  "SELECT li.property1 as property1_id ,pr1.name as property1_name FROM " . tablename('eso_sale_goods_property_list')." li ";
        $property_sql .= " left join " . tablename('eso_sale_goods_property1') . " pr1 on li.property1=pr1.id ";
        $property_sql .= " WHERE li.uniacid = '".$uniacid."' and li.goods_id ='".$goods_id."' ";
        $property1_list  = pdo_fetchall($property_sql);
        $detail['property_1']['property_list'] = $property1_list;

        $detail['property_2']['name'] = $detail['property2'];
        $goods_id = $detail['id'];
        $property_sql =  "SELECT li.property2 as property2_id ,pr2.name as property2_name FROM " . tablename('eso_sale_goods_property_list')." li ";
        $property_sql .= " left join " . tablename('eso_sale_goods_property2') . " pr2 on li.property2=pr2.id ";
        $property_sql .= " WHERE li.uniacid = '".$uniacid."' and li.goods_id ='".$goods_id."' ";
        $property2_list  = pdo_fetchall($property_sql);
        $detail['property_2']['property_list'] = $property2_list;
    }
    $ret = array();
    $ret['code']    = '200';
    $ret['message'] = '';
    $ret['content'] = $detail;
    echo json_encode($ret);
    return;
}

if($do == 'cart') {
    global $_W, $_GPC;
    $from_user = $_GPC['uid'];
    $_W['uniacid'] = $_GPC['uniacid'];//8
    //$from_user = '1064';// please remove after complete member logic
    $op = $_GPC['op'];
    if ($op == 'add') {
        $goodsid = intval($_GPC['goodsid']);
        $total = intval($_GPC['total']);
        $total = empty($total) ? 1 : $total;
        $optionid = intval($_GPC['optionid']);
        $property1 = intval($_GPC['property1']);
        $property2 = intval($_GPC['property2']);
        $good_sql = "SELECT go.id, go.type, go.total, go.marketprice, go.maxbuy FROM " . tablename('eso_sale_goods') . " go left join  " . tablename('eso_sale_goods_property_list') . " gl ";
        $good_sql .= " on go.id= gl.goods_id WHERE go.id = '".$goodsid."' and gl.property1='".$property1."' and gl.property2='".$property2."' ";
        $goods = pdo_fetch($good_sql);

        if (empty($goods)) {
            $ret = array();
            $ret['code']    = '404';
            $ret['message'] = '抱歉，该商品不存在或是已经被删除！';
            echo json_encode($ret);
            return;
        }
        $marketprice = $goods['marketprice'];
        if (!empty($optionid)) {
            $option = pdo_fetch("select marketprice from " . tablename('eso_sale_goods_option') . " where id=:id limit 1", array(":id" => $optionid));
            if (!empty($option)) {
                $marketprice = $option['marketprice'];
            }
        }

        $row = pdo_fetch("SELECT id, total FROM " . tablename('eso_sale_cart') . " WHERE from_user = :from_user AND uniacid = '{$_W['uniacid']}' AND goodsid = :goodsid  and optionid=:optionid and property1= :property1 and property2= :property2", array(':from_user' => $from_user, ':goodsid' => $goodsid, ':optionid' => $optionid,':property1' => $property1,':property2'=>$property2));
        if ($row == false) {
            //不存在
            $data = array(
                'uniacid' => $_W['uniacid'],
                'goodsid' => $goodsid,
                'goodstype' => $goods['type'],
                'marketprice' => $marketprice,
                'from_user' => $from_user,
                'total' => $total,
                'optionid' => $optionid,
                'property1' => $property1,
                'property2' => $property2
            );
            pdo_insert('eso_sale_cart', $data);
        } else {
            //累加最多限制购买数量
            $t = $total + $row['total'];
            if (!empty($goods['maxbuy'])) {
                if ($t > $goods['maxbuy']) {
                    $t = $goods['maxbuy'];
                }
            }
            //存在
            $data = array(
                'marketprice' => $marketprice,
                'total' => $t,
                'optionid' => $optionid
            );
            pdo_update('eso_sale_cart', $data, array('id' => $row['id']));
        }

        /////
        $carttotal = pdo_fetchcolumn("select sum(total) from " . tablename('eso_sale_cart') . " where uniacid = '{$_W['uniacid']}' and from_user='" . $from_user . "'");
        if(empty($carttotal)) $carttotal = 0 ;
        ///get total count
        $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_cart') . " WHERE  uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "'");
        $totalprice = 0;
        if (!empty($list)) {
            foreach ($list as $item) {
                $goods = pdo_fetch("SELECT  title, thumb, marketprice, unit, total,maxbuy FROM " . tablename('eso_sale_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
                if(!empty($goods))
                    $goods['thumb'] = $public_path.'/golf/attachment/'.$goods['thumb'];
                //属性
                $option = pdo_fetch("select title,marketprice,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));

                if ($option) {
                    $goods['title'] = $goods['title'];
                    $goods['optionname'] = $option['title'];
                    $goods['marketprice'] = $option['marketprice'];
                    $goods['total'] = $option['stock'];
                }
                $item['goods'] = $goods;
                $item['totalprice'] = (floatval($goods['marketprice']) * intval($item['total']));
                $totalprice += $item['totalprice'];
            }
        }
        $result = array(
            'result' => 1,
            'total' => $carttotal
        );
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = '';
        $ret['content'] = $result;
        $ret['totalprice'] = $totalprice;
        echo json_encode($ret);
        return;

    } else if ($op == 'clear') {
        pdo_delete('eso_sale_cart', array('from_user' => $from_user, 'uniacid' => $_W['uniacid']));
        $result = array(
            'result' => 1
        );
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = '';
        $ret['content'] = $result;
        echo json_encode($ret);
        return;
    } else if ($op == 'remove') {
        $id = intval($_GPC['cartid']);
        pdo_delete('eso_sale_cart', array('from_user' => $from_user, 'uniacid' => $_W['uniacid'], 'id' => $id));
        //
        $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_cart') . " WHERE  uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "'");
        $totalprice = 0;
        if (!empty($list)) {
            foreach ($list as $item) {
                $goods = pdo_fetch("SELECT  title, thumb, marketprice, unit, total,maxbuy FROM " . tablename('eso_sale_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
                if(!empty($goods))
                    $goods['thumb'] = $public_path.'/golf/attachment/'.$goods['thumb'];
                //属性
                $option = pdo_fetch("select title,marketprice,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));

                if ($option) {
                    $goods['title'] = $goods['title'];
                    $goods['optionname'] = $option['title'];
                    $goods['marketprice'] = $option['marketprice'];
                    $goods['total'] = $option['stock'];
                }
                $item['goods'] = $goods;
                $item['totalprice'] = (floatval($goods['marketprice']) * intval($item['total']));
                $totalprice += $item['totalprice'];
            }
        }
        $result = array(
            'result' => 1
        );
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = '';
        $ret['content'] = $result;
        $ret['totalprice'] = $totalprice;
        echo json_encode($ret);
    } else if ($op == 'update') {
        $id = intval($_GPC['cartid']);
        $num = intval($_GPC['total']);
        $sql = "update " . tablename('eso_sale_cart') . " set total=$num where id=:id";
        pdo_query($sql, array(":id" => $id));
        //
        $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_cart') . " WHERE  uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "'");
        $totalprice = 0;
        if (!empty($list)) {
            foreach ($list as $item) {
                $goods = pdo_fetch("SELECT  title, thumb, marketprice, unit, total,maxbuy FROM " . tablename('eso_sale_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
                if(!empty($goods))
                    $goods['thumb'] = $public_path.'/golf/attachment/'.$goods['thumb'];
                //属性
                $option = pdo_fetch("select title,marketprice,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));

                if ($option) {
                    $goods['title'] = $goods['title'];
                    $goods['optionname'] = $option['title'];
                    $goods['marketprice'] = $option['marketprice'];
                    $goods['total'] = $option['stock'];
                }
                $item['goods'] = $goods;
                $item['totalprice'] = (floatval($goods['marketprice']) * intval($item['total']));
                $totalprice += $item['totalprice'];
            }
        }
        //
        $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_cart') . " WHERE  id = '" . $id . "'");
        $details = array();
        $totalprice = 0;
        if (!empty($list)) {
            foreach ($list as $item) {
                $goods = pdo_fetch("SELECT  title, thumb, marketprice, unit, total,maxbuy FROM " . tablename('eso_sale_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
                if(!empty($goods))
                    $goods['thumb'] = $public_path.'/golf/attachment/'.$goods['thumb'];
                //属性
                $option = pdo_fetch("select title,marketprice,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));

                if ($option) {
                    $goods['title'] = $goods['title'];
                    $goods['optionname'] = $option['title'];
                    $goods['marketprice'] = $option['marketprice'];
                    $goods['total'] = $option['stock'];
                }
                $item['goods'] = $goods;
                $item['totalprice'] = (floatval($goods['marketprice']) * intval($item['total']));
                $totalprice += $item['totalprice'];
                $details[] = $item;
            }
        }

        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = '';
        $ret['content'] = $details;
        $ret['totalprice'] = $totalprice;
        echo json_encode($ret);

    } else {
        $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_cart') . " WHERE  uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "'");
        $details = array();
        $totalprice = 0;
        if (!empty($list)) {
            foreach ($list as $item) {
                $goods = pdo_fetch("SELECT  title, thumb, marketprice, unit, total,maxbuy, property1, property2 FROM " . tablename('eso_sale_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
                if(!empty($goods)) {
                    $goods['thumb'] = $public_path . '/golf/attachment/' . $goods['thumb'];
                    $goods_id = $item['goodsid'];
                    $good_sql = "SELECT gp1.id as property1_id, gp1.name as property1_name, gp2.id as property2_id, gp2.name as property2_name  FROM " . tablename('eso_sale_goods_property_list') . " gl ";
                    $good_sql .= " left join ".tablename('eso_sale_goods_property1')." gp1 on gl.property1= gp1.id ";
                    $good_sql .=" left join ".tablename('eso_sale_goods_property2')." gp2 on gl.property2= gp2.id WHERE gl.goods_id='".$goods_id."' ";
                    $good_sql .=" and gl.uniacid= '{$_W['uniacid']}' and gp1.uniacid = '{$_W['uniacid']}' and gp2.uniacid='{$_W['uniacid']}' ";
                    $good_property = pdo_fetch($good_sql);
                    if(!empty($good_property)){
                        $goods['property_1']['name'] = $goods['property1'];
                        $goods['property_1']['property_id'] = $good_property['property1_id'];
                        $goods['property_1']['property_name'] = $good_property['property1_name'];
                        $goods['property_2']['name'] = $goods['property2'];
                        $goods['property_2']['property_id'] = $good_property['property2_id'];
                        $goods['property_2']['property_name'] = $good_property['property2_name'];
                    }
                }
                //属性
                $option = pdo_fetch("select title,marketprice,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));

                if ($option) {
                    $goods['title'] = $goods['title'];
                    $goods['optionname'] = $option['title'];
                    $goods['marketprice'] = $option['marketprice'];
                    $goods['total'] = $option['stock'];
                }
                $item['goods'] = $goods;
                $item['totalprice'] = (floatval($goods['marketprice']) * intval($item['total']));
                $totalprice += $item['totalprice'];
                $details[] = $item;
            }
        }
        $ret = array();
        $ret['code']    = '200';
        $ret['message'] = '';
        $ret['content'] = $details;
        $ret['totalprice'] = $totalprice;
        echo json_encode($ret);
        return;
    }
}

//order
if($do == 'order') {
    global $_W, $_GPC;
    $from_user = $_GPC['uid'];
    $_W['uniacid'] = $_GPC['uniacid'];
    $uniacid = $_W['uniacid'];
    $ordertype = $_GPC['ordertype'];
    $op = $_GPC['op'] ? $_GPC['op'] : 'display';

    $totalprice = 0;
    $allgoods = array();
    $free_shipping = 0;
    $freight = 0;

    $id = intval($_GPC['goodsid']);
    $optionid = intval($_GPC['optionid']);
    $total = intval($_GPC['goodscount']);//if order is direct, count number
    if (empty($total)) {
        $total = 1;
    }
    $direct = false; //是否是直接购买
    if($ordertype == 'goods') $direct = true;
    $returnurl = ""; //当前连接

    if (!empty($id)) { // direct==true
        $item = pdo_fetch("select id,thumb,ccate,title,weight,marketprice,total,type,totalcnf,sales,unit,istime,timeend,free_shipping,freight  from " . tablename("eso_sale_goods") . " where id=:id limit 1", array(":id" => $id));

        if ($item['istime'] == 1) {
            if (time() > $item['timeend']) {
                $ret = array();
                $ret['code']    = '401';
                $ret['message'] = '抱歉，商品限购时间已到，无法购买了！'; //Sorry, the purchase time of the goods has expired and you can't purchase it!
                echo json_encode($ret);
                return;
            }
        }

        if (!empty($optionid)) {
            $option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $optionid));
            if ($option) {
                $item['optionid'] = $optionid;
                $item['title'] = $item['title'];
                $item['optionname'] = $option['title'];
                $item['marketprice'] = $option['marketprice'];
                $item['weight'] = $option['weight'];
            }
        }
        $item['stock'] = $item['total'];
        $item['total'] = $total;
        $item['totalprice'] = $total * $item['marketprice'];

        $free_shipping += $item['free_shipping'];


        if( $item['totalprice'] < $item['free_shipping'] ){
            $freight += $item['freight'];
        }

        $allgoods[] = $item;
        $totalprice += $item['totalprice'];
        if ($item['type'] == 1) {
            $needdispatch = true;
        }
        //$direct = true;
        //$returnurl = $this->mturl("confirm", array("id" => $id, "optionid" => $optionid, "total" => $total));
    }

    if (!$direct) {// if this is request from cart
        $cartid = $_GPC['cartid'];
        //如果不是直接购买（从购物车购买）
        $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_cart') . " WHERE  uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "' AND id in (".$cartid.")");
        if (!empty($list)) {
            foreach ($list as &$g) {
                $item = pdo_fetch("select id,thumb,ccate,title,weight,marketprice,total,type,totalcnf,sales,unit,free_shipping,freight from " . tablename("eso_sale_goods") . " where id=:id limit 1", array(":id" => $g['goodsid']));
                //属性
                $option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("eso_sale_goods_option") . " where id=:id limit 1", array(":id" => $g['optionid']));
                if ($option) {
                    $item['optionid'] = $g['optionid'];
                    $item['title'] = $item['title'];
                    $item['optionname'] = $option['title'];
                    $item['marketprice'] = $option['marketprice'];
                    $item['weight'] = $option['weight'];
                }
                $item['stock'] = $item['total'];
                $item['total'] = $g['total'];
                $item['totalprice'] = $g['total'] * $item['marketprice'];
                $allgoods[] = $item;
                $totalprice += $item['totalprice'];
                if( $item['totalprice'] < $item['free_shipping'] ){
                    $freight += $item['freight'];
                }

                if ($item['type'] == 1) {
                    $needdispatch = true;
                }
            }
            unset($g);
        }
        //$returnurl = $this->mturl("confirm");
    }

    if (count($allgoods) <= 0) {
        //header("location: " . $this->mturl('myorder'));
        //exit();
    }
    //配送方式
    $dispatch = pdo_fetchall("select id,dispatchname,firstprice,firstweight,secondprice,secondweight from " . tablename("eso_sale_dispatch") . " WHERE uniacid = {$_W['uniacid']}");

    foreach ($dispatch as &$d) {
        $price = 0;
        $d['price'] = $d['firstprice'];
    }
    $d['price'] = $freight;
    unset($d);


        // var_dump($_GPC);exit;
        $address = pdo_fetch("SELECT * FROM " . tablename('eso_sale_address') . " WHERE id = :id", array(':id' => intval($_GPC['addressid'])));
        if (empty($address)) {
            //message('抱歉，请您填写收货地址！');
        }
        //商品价格
        $goodsprice = 0;
        foreach ($allgoods as $row) {
            if ($item['stock'] != -1 && $row['total'] > $item['stock']) {
                $ret = array();
                $ret['code']    = '402';
                $ret['message'] = '此商品库存不足！'; //This item has insufficient stock!
                echo json_encode($ret);
                return;
            }
            $goodsprice += $row['totalprice'];
        }
        //运费        
        $dispatchid = 0;
        $dispatchprice =intval($_GPC['dispatch']);
        
        //$shareId = $this->getShareId();
        $shareId    = 0;
        $user   = pdo_fetch("SELECT uid FROM " . tablename('mc_members') . " WHERE tencentuserid = :vip_userid and uniacid= :uniacid ", array(':vip_userid' => $from_user,':uniacid'=>$_W['uniacid']));
        $data = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $user['uid'],
            'from_user' => $from_user,
            'ordersn' => date('md') . random(4, 1),
            'price' => $goodsprice + $dispatchprice,
            'dispatchprice' => $dispatchprice,
            'goodsprice' => $goodsprice,
            'status' => 0,
            'sendtype' => intval($_GPC['sendtype']),
            'dispatch' => $dispatchid,
            'paytype' => '2',
            'goodstype' => intval($cart['type']),
            'remark' => $_GPC['remark'],
            'addressid' => $address['id'],
            'createtime' => TIMESTAMP, 'shareid' => $shareId
        );

        pdo_insert('eso_sale_order', $data);
        $orderid = pdo_insertid();
        //插入订单商品
        foreach ($allgoods as $row) {
            if (empty($row)) {
                continue;
            }
            $d = array(
                'uniacid' => $_W['uniacid'],
                'goodsid' => $row['id'],
                'orderid' => $orderid,
                'total' => $row['total'],
                'price' => $row['marketprice'],
                'createtime' => TIMESTAMP,
                'optionid' => $row['optionid']
            );
            $o = pdo_fetch("select title from " . tablename('eso_sale_goods_option') . " where id=:id limit 1", array(":id" => $row['optionid']));
            if (!empty($o)) {
                $d['optionname'] = $o['title'];
            }
            //获取商品id
            $ccate = $row['ccate'];
            //$commission = pdo_fetchcolumn( " SELECT commission FROM ".tablename('eso_sale_category')."  WHERE id=".$ccate);
            $commission = pdo_fetchcolumn(" SELECT commission FROM " . tablename('eso_sale_goods') . "  WHERE id=" . $row['id']);
            $commission2 = pdo_fetchcolumn(" SELECT commission2 FROM " . tablename('eso_sale_goods') . "  WHERE id=" . $row['id']);
            $commission3 = pdo_fetchcolumn(" SELECT commission3 FROM " . tablename('eso_sale_goods') . "  WHERE id=" . $row['id']);

            if ($commission == false || $commission == null || $commission < 0) {
                //$commission = $this->module['config']['globalCommission'];
            }
            if ($commission2 == false || $commission2 == null || $commission2 < 0) {
                //$commission2 = $this->module['config']['globalCommission2'];
            }
            if ($commission3 == false || $commission3 == null || $commission3 < 0) {
                //$commission3 = $this->module['config']['globalCommission3'];
            }
            $commissionTotal = $row['marketprice'] * $commission / 100;
            $d['commission'] = $commissionTotal;
            $commissionTotal2 = $commissionTotal * $commission2 / 100;
            $d['commission2'] = $commissionTotal2;
            $commissionTotal3 = $commissionTotal2 * $commission3 / 100;
            $d['commission3'] = $commissionTotal3;
            pdo_insert('eso_sale_order_goods', $d);
        }
        //清空购物车
        if (!$direct) {
            $cartids = explode("," , $_GPC['cartid']);
            foreach( $cartids as $id) {
                pdo_delete("eso_sale_cart", array("uniacid" => $_W['uniacid'], "from_user" => $from_user,'id'=>$id));
            }
        }
        //$this->setCartGoods(array());
        //变更商品库存
        setOrderStock($orderid);

//        $ret = array();
//        $ret['code']    = '200';
//        $ret['message'] = '提交订单成功,现在跳转到付款页面...'; //Submit order successfully, now go to payment page...
//        $ret['oderid']  = $orderid;
//        echo json_encode($ret);

    /*$carttotal = $this->getCartTotal();
    $profile = fans_search($from_user, array('resideprovince', 'residecity', 'residedist', 'address', 'realname', 'mobile'));
    $row = pdo_fetch("SELECT * FROM " . tablename('eso_sale_address') . " WHERE isdefault = 1 and openid = :openid limit 1", array(':openid' => $from_user));
    */
    ///////////////////////////
    $input = new WxPayUnifiedOrder();
    //$input->SetAppid('wx2704af054a7e345d');
    //$input->SetMch_id('1263336301');
    $input->SetNonce_str(random(32));
    $input->SetBody("test");
    $input->SetAttach("8");
    $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
    $input->SetTotal_fee("1");
    //$input->SetSpbill_create_ip('45.56.152.156');
    $input->SetTime_start(date("YmdHis"));
    $input->SetTime_expire(date("YmdHis", time() + 600));
    $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
    $input->SetTrade_type("APP");
    //$input->SetOpenid($openId);
    //$input->SetDetail();
    //<detail><![CDATA[{ "goods_detail" :[ { "goods_id":"iphone6s_16G", "wxpay_goods_id":"1001", "goods_name":"iPhone6s 16G", "quantity":1, "price":528800, "goods_category":"123456", "body ":"Apple phone" }, { "goods_id":"iphone6s_32G", "wxpay_goods_id":"1002", "goods_name":"iPhone6s 32G", "quantity":1, "price":608800, "goods_category": "123789", "body": "Apple phone" } ] ]]>></detail>
    $order = WxPayApi::unifiedOrder($input);
    $order['package'] = "Sign=WXPay";
    $order['timestamp'] = TIMESTAMP;
    $return_code = $order['return_code'];
    $ret = array();
    if($return_code == 'FAIL') {
        $ret['code'] = '404';
        $ret['content'] = $order;
        echo json_encode($ret);
        return;
    }else {
        $ret['code'] = '200';
        $ret['message'] = 'success';
        $ret['content'] = $order;
        echo json_encode($ret);
        return;
    }
    ///////////////////////////
}

//pay
if($do == 'pay') {
    global $_W, $_GPC;
    $from_user  = $_GPC['uid'];
    $orderid    = intval($_GPC['orderid']);

    $order = pdo_fetch("SELECT * FROM " . tablename('eso_sale_order') . " WHERE id = :id", array(':id' => $orderid));
    if ($order['status'] != '0') {
        $ret = array();
        $ret['code']    = '401';
        $ret['message'] = '抱歉，您的订单已经付款或是被关闭，请重新进入付款！';//Sorry, your order has been paid or closed, please re-enter payment!
        echo json_encode($ret);
        return;
    }

    $ordergoods = pdo_fetchall("SELECT goodsid, total,optionid FROM " . tablename('eso_sale_order_goods') . " WHERE orderid = '{$orderid}'", array(), 'goodsid');
    if (!empty($ordergoods)) {
        $goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total,credit FROM " . tablename('eso_sale_goods') . " WHERE id IN ('" . implode("','", array_keys($ordergoods)) . "')");
    }

    //check balance
    $balance = 100;
    //if ($order['paytype'] == 1 && $_W['fans']['credit2'] < $order['price']) {
    if ($order['paytype'] == 1 && $balance < $order['price']) {
        $ret = array();
        $ret['code']    = '402';
        $ret['message'] = '抱歉，您帐户的余额不够支付该订单，请充值！';//the balance of your account is not enough to pay for this order. Please recharge!
        echo json_encode($ret);
        return;
    }
    if ($order['price'] == '0') {
        payResult(array('tid' => $orderid, 'from' => 'return', 'type' => 'credit2'));
        //exit;
    }

    //email function
    /*
    if (!empty($this->module['config']['noticeemail'])) {

        $address = pdo_fetch("SELECT * FROM " . tablename('eso_sale_address') . " WHERE id = :id", array(':id' => $order['addressid']));

        $body = "<h3>购买商品清单</h3> <br />";

        if (!empty($goods)) {
            foreach ($goods as $row) {
                $body .= "名称：{$row['title']} ，数量：{$ordergoods[$row['id']]['total']} <br />";
            }
        }
        $paytype = $order['paytype'] == '3' ? '货到付款' : '已付款';
        $body .= "<br />总金额：{$order['price']}元 （{$paytype}）<br />";
        $body .= "<h3>购买用户详情</h3> <br />";
        $body .= "真实姓名：$address[realname] <br />";
        $body .= "地区：$address[province] - $address[city] - $address[area]<br />";
        $body .= "详细地址：$address[address] <br />";
        $body .= "手机：$address[mobile] <br />";
        load()->func('communication');
        ihttp_email($this->module['config']['noticeemail'], '微商城订单提醒', $body);
    }
    */
    pdo_update('eso_sale_order', array('status' => '1', 'paytype' => '3'), array('id' => $orderid));

    // 商品编号
    $sql = 'SELECT `goodsid` FROM ' . tablename('eso_sale_order_goods') . " WHERE `orderid` = :orderid";
    $goodsId = pdo_fetchcolumn($sql, array(':orderid' => $orderid));
    // 商品名称
    $sql = 'SELECT `title` FROM ' . tablename('eso_sale_goods') . " WHERE `id` = :id";
    $goodsTitle = pdo_fetchcolumn($sql, array(':id' => $goodsId));

    $params['tid'] = $orderid;
    $params['user'] = $from_user;
    $params['fee'] = $order['price'];
    $params['title'] = $goodsTitle;
    $params['ordersn'] = $order['ordersn'];
    $params['virtual'] = $order['goodstype'] == 2 ? true : false;

    $ret = array();
    $ret['code']    = '200';
    $ret['message'] = '订单提交成功，请您收到货时付款！';//Order submitted successfully, please pay when you receive the goods!
    $ret['content'] = $params;
    echo json_encode($ret);
    return;

}

//get Myorder list
if($do == 'myorder') {
    global $_W, $_GPC;
    $from_user      = $_GPC['uid'];
    $_W['uniacid']  = $_GPC['uniacid'];
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

        $status = intval($_GPC['status']);
        $where = " uniacid = '{$_W['uniacid']}' AND from_user = '" . $from_user . "'";;
        if ($status == 2) {
            $where .= " and ( status=1 or status=2 )";
        } else {
            $where .= " and status=$status";
        }

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
        $carttotal = getCartTotal();
        //load()->model('mc');
        //$fans = mc_fetch($_W['member']['uid']);
        $content = array();
        foreach ($list as $li) {
            $good_list = $li['goods'];
            $goods = array();
            foreach ($good_list as $gl) {
                $gl['thumb'] = $public_path.'/golf/attachment/'.$gl['thumb'];
                $goods[] = $gl;
            }
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

//shop_week_recommend
if($do == 'shop_week_recommend') {
    $uniacid = $_GPC['uniacid'];
    $good_page_size  = 1;
    $article_page_size  = 2;
    $page_num   = $_GPC['page_num'];
    $good_start_pos = ($page_num -1) * ($good_page_size);
    $article_start_pos = ($page_num -1) * ($article_page_size);
    $result = array();
    $good_sql    = "select id, thumb from ims_eso_sale_goods where uniacid = '".$uniacid."' order by id desc limit " . $good_start_pos . "," . $good_page_size;
    $good_result = pdo_fetchall($good_sql);
    foreach ($good_result as $go) {
        $good_result[0]['thumb'] = $public_path.'/golf/attachment/'.$go['thumb'];
    }
    $result['good'] = $good_result;
    $article_sql    = "select id,thumb from ims_golf_article where uniacid = '".$uniacid."' order by id desc limit " . $article_start_pos . "," . $article_page_size;
    $article_result = pdo_fetchall($article_sql);
    $a = 0;
    foreach ($article_result as $ar) {
        $article_result[$a]['thumb'] = $public_path.'/golf/attachment/'.$ar['thumb'];
        $a++;
    }
    $result['article'] = $article_result;
    $ret = array();
    $ret['code']    = '200';
    $ret['message'] = 'success';
    $ret['content'] = $result;
    echo json_encode($ret);
    return;
}
if($do == 'shop_adv_good') {
    $ret    = array();
    $result = array();
    $list  = pdo_fetchall("SELECT id, xsthumb, thumb, marketprice, title  FROM ims_eso_sale_goods where isadv= '1' ");
    if(count($list) >= 4) {
        $list_key = array_rand($list, 4);
        array_push($result, $list[$list_key[0]]);
        array_push($result, $list[$list_key[1]]);
        array_push($result, $list[$list_key[2]]);
        array_push($result, $list[$list_key[3]]);
    }else {
        $result = $list;
    }
    $i = 0;
    foreach($result as $ba) {
        $result[$i]['thumb'] = $public_path.'/golf/attachment/'.$result[$i]['thumb'];
        $result[$i]['xsthumb'] = $public_path.'/golf/attachment/'.$result[$i]['xsthumb'];
        $i++;
    }
    $ret['code'] = '200';
    $ret['message'] = '';
    $ret['content'] = $result;
    echo json_encode($ret);
    return;
}

