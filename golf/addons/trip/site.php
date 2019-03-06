<?php

defined('IN_IA') or exit('Access Denied');

session_start();

class TripModuleSite extends WeModuleSite
{

    public function __web($f_name)
    {
        global $_W, $_GPC;
        checklogin();
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
        include_once 'web/' . strtolower(substr($f_name, 5)) . '.php';
    }

    public function __mobile($f_name)
    {
        global $_W, $_GPC;
        $from_user = $this->getFromUser();
        $uniacid = $_W['uniacid'];
        $op = $_GPC['op'] ? $_GPC['op'] : 'display';

        include_once 'mobile/' . strtolower(substr($f_name, 8)) . '.php';
    }


    public function doWebCategory()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update('trip_category', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
            }
            $children = array();
            $category = pdo_fetchall("SELECT * FROM " . tablename('trip_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder DESC");
            foreach ($category as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($category[$index]);
                }
            }
            include $this->template('category');
        } elseif ($operation == 'post') {
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $category = pdo_fetch("SELECT * FROM " . tablename('trip_category') . " WHERE id = '$id'");
            } else {
                $category = array(
                    'displayorder' => 0,
                );
            }
            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, name FROM " . tablename('trip_category') . " WHERE id = '$parentid'");
                if (empty($parent)) {
                    message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
                }
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['catename'])) {
                    message('抱歉，请输入分类名称！');
                }
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'name' => $_GPC['catename'],
                    'enabled' => intval($_GPC['enabled']),
                    'displayorder' => intval($_GPC['displayorder']),
                    'isrecommand' => intval($_GPC['isrecommand']),
                    //    'commission' => intval($_GPC['commission']),
                    'description' => $_GPC['description'],
                    'parentid' => intval($parentid),
                );
                if (!empty($_FILES['thumb']['tmp_name'])) {
                    file_delete($_GPC['thumb_old']);
                    $upload = file_upload($_FILES['thumb']);
                    if (is_error($upload)) {
                        message($upload['message'], '', 'error');
                    }
                    $data['thumb'] = $upload['path'];
                }

                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update('trip_category', $data, array('id' => $id));
                } else {
                    pdo_insert('trip_category', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
            }
            include $this->template('category');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $category = pdo_fetch("SELECT id, parentid FROM " . tablename('trip_category') . " WHERE id = '$id'");
            if (empty($category)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('category', array('op' => 'display')), 'error');
            }
            pdo_delete('trip_category', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
        }

    }

    public function doWebGoods()
    {
        global $_GPC, $_W;
        load()->func('tpl');

        $category = pdo_fetchall("SELECT * FROM " . tablename('trip_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder DESC", array(), 'id');
        if (!empty($category)) {
            $children = '';
            foreach ($category as $cid => $cate) {
                if (!empty($cate['parentid'])) {
                    $children[$cate['parentid']][$cate['id']] = array($cate['id'], $cate['name']);
                }
            }
        }

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'post') {


            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('trip_goods') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，商品不存在或是已经删除！', '', 'error');
                }
                $allspecs = pdo_fetchall("select * from " . tablename('trip_spec') . " where goodsid=:id order by displayorder asc", array(":id" => $id));
                foreach ($allspecs as &$s) {
                    $s['items'] = pdo_fetchall("select * from " . tablename('trip_spec_item') . " where specid=:specid order by displayorder asc", array(":specid" => $s['id']));
                }
                unset($s);

                $params = pdo_fetchall("select * from " . tablename('trip_goods_param') . " where goodsid=:id order by displayorder asc", array(':id' => $id));
                $piclist = unserialize($item['thumb_url']);
                //处理规格项
                $html = "";
                $options = pdo_fetchall("select * from " . tablename('trip_goods_option') . " where goodsid=:id order by id asc", array(':id' => $id));
                $piclist1 = unserialize($item['thumb_url']);
                $piclist = array();
                if (is_array($piclist1)) {
                    foreach ($piclist1 as $p) {
                        $piclist[] = is_array($p) ? $p['attachment'] : $p;
                    }
                }

                //排序好的specs
                $specs = array();
                //找出数据库存储的排列顺序
                if (count($options) > 0) {
                    $specitemids = explode("_", $options[0]['specs']);
                    foreach ($specitemids as $itemid) {
                        foreach ($allspecs as $ss) {
                            $items = $ss['items'];
                            foreach ($items as $it) {
                                if ($it['id'] == $itemid) {
                                    $specs[] = $ss;
                                    break;
                                }
                            }
                        }
                    }

                    $html = '<table  class="tb spectable" style="border:1px solid #ccc;"><thead><tr>';

                    $len = count($specs);
                    $newlen = 1; //多少种组合
                    $h = array(); //显示表格二维数组
                    $rowspans = array(); //每个列的rowspan


                    for ($i = 0; $i < $len; $i++) {
                        //表头
                        $html .= "<th>" . $specs[$i]['title'] . "</th>";

                        //计算多种组合
                        $itemlen = count($specs[$i]['items']);
                        if ($itemlen <= 0) {
                            $itemlen = 1;
                        }
                        $newlen *= $itemlen;

                        //初始化 二维数组
                        $h = array();
                        for ($j = 0; $j < $newlen; $j++) {
                            $h[$i][$j] = array();
                        }
                        //计算rowspan
                        $l = count($specs[$i]['items']);
                        $rowspans[$i] = 1;
                        for ($j = $i + 1; $j < $len; $j++) {
                            $rowspans[$i] *= count($specs[$j]['items']);
                        }
                    }
                    //   print_r($rowspans);exit();

                    $html .= '<th><div class="input-append input-prepend"><span class="add-on">库存</span><input type="text" class="span1 option_stock_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_stock\');"></a></span></div></th>';
                    $html .= '<th><div class="input-append input-prepend"><span class="add-on">销售价格</span><input type="text" class="span1 option_marketprice_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_marketprice\');"></a></span></div><br/></th>';
                    $html .= '<th><div class="input-append input-prepend"><span class="add-on">市场价格</span><input type="text" class="span1 option_productprice_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_productprice\');"></a></span></div></th>';
                    $html .= '<th><div class="input-append input-prepend"><span class="add-on">成本价格</span><input type="text" class="span1 option_costprice_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_costprice\');"></a></span></div></th>';
                    $html .= '<th><div class="input-append input-prepend"><span class="add-on">重量(克)</span><input type="text" class="span1 option_weight_all"  VALUE=""/><span class="add-on"><a href="javascript:;" class="icon-hand-down" title="批量设置" onclick="setCol(\'option_weight\');"></a></span></div></th>';
                    $html .= '</tr>';
                    for ($m = 0; $m < $len; $m++) {
                        $k = 0;
                        $kid = 0;
                        $n = 0;
                        for ($j = 0; $j < $newlen; $j++) {
                            $rowspan = $rowspans[$m]; //9
                            if ($j % $rowspan == 0) {
                                $h[$m][$j] = array("html" => "<td rowspan='" . $rowspan . "'>" . $specs[$m]['items'][$kid]['title'] . "</td>", "id" => $specs[$m]['items'][$kid]['id']);
                                // $k++; if($k>count($specs[$m]['items'])-1) { $k=0; }
                            } else {
                                $h[$m][$j] = array("html" => "", "id" => $specs[$m]['items'][$kid]['id']);
                            }
                            $n++;
                            if ($n == $rowspan) {
                                $kid++;
                                if ($kid > count($specs[$m]['items']) - 1) {
                                    $kid = 0;
                                }
                                $n = 0;
                            }
                        }
                    }

                    $hh = "";
                    for ($i = 0; $i < $newlen; $i++) {
                        $hh .= "<tr>";
                        $ids = array();
                        for ($j = 0; $j < $len; $j++) {
                            $hh .= $h[$j][$i]['html'];
                            $ids[] = $h[$j][$i]['id'];
                        }
                        $ids = implode("_", $ids);

                        $val = array("id" => "", "title" => "", "stock" => "", "costprice" => "", "productprice" => "", "marketprice" => "", "weight" => "");
                        foreach ($options as $o) {
                            if ($ids === $o['specs']) {
                                $val = array("id" => $o['id'],
                                    "title" => $o['title'],
                                    "stock" => $o['stock'],
                                    "costprice" => $o['costprice'],
                                    "productprice" => $o['productprice'],
                                    "marketprice" => $o['marketprice'],
                                    "weight" => $o['weight']);
                                break;
                            }
                        }

                        $hh .= '<td>';
                        $hh .= '<input name="option_stock_' . $ids . '[]"  type="text" class="span1 option_stock option_stock_' . $ids . '" value="' . $val['stock'] . '"/></td>';
                        $hh .= '<input name="option_id_' . $ids . '[]"  type="hidden" class="span1 option_id option_id_' . $ids . '" value="' . $val['id'] . '"/>';
                        $hh .= '<input name="option_ids[]"  type="hidden" class="span1 option_ids option_ids_' . $ids . '" value="' . $ids . '"/>';
                        $hh .= '<input name="option_title_' . $ids . '[]"  type="hidden" class="span1 option_title option_title_' . $ids . '" value="' . $val['title'] . '"/>';
                        $hh .= '</td>';
                        $hh .= '<td><input name="option_marketprice_' . $ids . '[]" type="text" class="span1 option_marketprice option_marketprice_' . $ids . '" value="' . $val['marketprice'] . '"/></td>';
                        $hh .= '<td><input name="option_productprice_' . $ids . '[]" type="text" class="span1 option_productprice option_productprice_' . $ids . '" " value="' . $val['productprice'] . '"/></td>';
                        $hh .= '<td><input name="option_costprice_' . $ids . '[]" type="text" class="span1 option_costprice option_costprice_' . $ids . '" " value="' . $val['costprice'] . '"/></td>';
                        $hh .= '<td><input name="option_weight_' . $ids . '[]" type="text" class="span1 option_weight option_weight_' . $ids . '" " value="' . $val['weight'] . '"/></td>';
                        $hh .= "</tr>";
                    }
                    $html .= $hh;
                    $html .= "</table>";
                }
            }
            if (empty($category)) {
                message('抱歉，请您先添加商品分类！', $this->createWebUrl('category', array('op' => 'post')), 'error');
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['goodsname'])) {
                    message('请输入商品名称！');
                }
                if (empty($_GPC['pcate'])) {
                    message('请选择商品分类！');
                }
                if (empty($_GPC['thumbs'])) {
                    $_GPC['thumbs'] = array();
                }
                $data = array(
                    'uniacid' => intval($_W['uniacid']),
                    'displayorder' => intval($_GPC['displayorder']),
                    'title' => $_GPC['goodsname'],
                    'pcate' => intval($_GPC['pcate']),
                    'ccate' => intval($_GPC['ccate']),
                    'type' => intval($_GPC['type']),
                    'isrecommand' => intval($_GPC['isrecommand']),
                    'ishot' => intval($_GPC['ishot']),
                    'isnew' => intval($_GPC['isnew']),
                    'isdiscount' => intval($_GPC['isdiscount']),
                    'istime' => intval($_GPC['istime']),
                    'timestart' => strtotime($_GPC['timestart']),
                    'timeend' => strtotime($_GPC['timeend']),
                    'description' => $_GPC['description'],
                    'content' => htmlspecialchars_decode($_GPC['content']),
                    'goodssn' => $_GPC['goodssn'],
                    'unit' => $_GPC['unit'],
                    'createtime' => TIMESTAMP,
                    'total' => intval($_GPC['total']),
                    'totalcnf' => intval($_GPC['totalcnf']),
                    'marketprice' => $_GPC['marketprice'],
                    'weight' => $_GPC['weight'],
                    'costprice' => $_GPC['costprice'],
                    'productprice' => $_GPC['productprice'],
                    'productsn' => $_GPC['productsn'],
                    'credit' => intval($_GPC['credit']),
                    'maxbuy' => intval($_GPC['maxbuy']),
                    'commission' => intval($_GPC['commission']),
                    'commission2' => intval($_GPC['commission2']),
                    'commission3' => intval($_GPC['commission3']),
                    'hasoption' => intval($_GPC['hasoption']),
                    'sales' => intval($_GPC['sales']),
                    'status' => intval($_GPC['status']),
                    'thumb' => $_GPC['thumb'],
                    'xsthumb' => $_GPC['xsthumb'],
                    'free_shipping'=>$_GPC['free_shipping'],
                    'freight'=>$_GPC['freight'],
                );

                if($data['freight'] == '-1'){
                    $freight = pdo_get('trip_dispatch' , array('uniacid'=>$_W['uniacid']));
                    $data['freight'] = $freight['firstprice'];
                }

                if (is_array($_GPC['thumbs'])) {
                    $data['thumb_url'] = serialize($_GPC['thumbs']);
                }

                if (empty($id)) {
                    pdo_insert('trip_goods', $data);
                    $id = pdo_insertid();
                } else {
                    unset($data['createtime']);
                    pdo_update('trip_goods', $data, array('id' => $id));
                }


                $totalstocks = 0;

                //处理自定义参数

                $param_ids = $_POST['param_id'];
                $param_titles = $_POST['param_title'];
                $param_values = $_POST['param_value'];
                $param_displayorders = $_POST['param_displayorder'];
                $len = count($param_ids);
                $paramids = array();
                for ($k = 0; $k < $len; $k++) {
                    $param_id = "";
                    $get_param_id = $param_ids[$k];
                    $a = array(
                        "title" => $param_titles[$k],
                        "value" => $param_values[$k],
                        "displayorder" => $k,
                        "goodsid" => $id,
                    );
                    if (!is_numeric($get_param_id)) {
                        pdo_insert("trip_goods_param", $a);
                        $param_id = pdo_insertid();
                    } else {
                        pdo_update("trip_goods_param", $a, array('id' => $get_param_id));
                        $param_id = $get_param_id;
                    }
                    $paramids[] = $param_id;
                }
                if (count($paramids) > 0) {
                    pdo_query("delete from " . tablename('trip_goods_param') . " where goodsid=$id and id not in ( " . implode(',', $paramids) . ")");
                } else {
                    pdo_query("delete from " . tablename('trip_goods_param') . " where goodsid=$id");
                }
//                if ($totalstocks > 0) {
//                    pdo_update("trip_goods", array("total" => $totalstocks), array("id" => $id));
//                }
                //处理商品规格
                $files = $_FILES;
                $spec_ids = $_POST['spec_id'];
                $spec_titles = $_POST['spec_title'];

                $specids = array();
                $len = count($spec_ids);
                $specids = array();
                $spec_items = array();
                for ($k = 0; $k < $len; $k++) {
                    $spec_id = "";
                    $get_spec_id = $spec_ids[$k];
                    $a = array(
                        "uniacid" => $_W['uniacid'],
                        "goodsid" => $id,
                        "displayorder" => $k,
                        "title" => $spec_titles[$get_spec_id]
                    );
                    if (is_numeric($get_spec_id)) {

                        pdo_update("trip_spec", $a, array("id" => $get_spec_id));
                        $spec_id = $get_spec_id;
                    } else {
                        pdo_insert("trip_spec", $a);
                        $spec_id = pdo_insertid();
                    }
                    //子项
                    $spec_item_ids = $_POST["spec_item_id_" . $get_spec_id];
                    $spec_item_titles = $_POST["spec_item_title_" . $get_spec_id];
                    $spec_item_shows = $_POST["spec_item_show_" . $get_spec_id];

                    $spec_item_oldthumbs = $_POST["spec_item_oldthumb_" . $get_spec_id];
                    $itemlen = count($spec_item_ids);
                    $itemids = array();


                    for ($n = 0; $n < $itemlen; $n++) {


                        $item_id = "";
                        $get_item_id = $spec_item_ids[$n];
                        $d = array(
                            "uniacid" => $_W['uniacid'],
                            "specid" => $spec_id,
                            "displayorder" => $n,
                            "title" => $spec_item_titles[$n],
                            "show" => $spec_item_shows[$n]
                        );
                        $f = "spec_item_thumb_" . $get_item_id;
                        $old = $spec_item_oldthumbs[$k];
                        if (!empty($files[$f]['tmp_name'])) {
                            $upload = file_upload($files[$f]);
                            if (is_error($upload)) {
                                message($upload['message'], '', 'error');
                            }
                            $d['thumb'] = $upload['path'];
                        } else if (!empty($old)) {
                            $d['thumb'] = $old;
                        }

                        if (is_numeric($get_item_id)) {
                            pdo_update("trip_spec_item", $d, array("id" => $get_item_id));
                            $item_id = $get_item_id;
                        } else {
                            pdo_insert("trip_spec_item", $d);
                            $item_id = pdo_insertid();
                        }
                        $itemids[] = $item_id;

                        //临时记录，用于保存规格项
                        $d['get_id'] = $get_item_id;
                        $d['id'] = $item_id;
                        $spec_items[] = $d;
                    }
                    //删除其他的
                    if (count($itemids) > 0) {
                        pdo_query("delete from " . tablename('trip_spec_item') . " where uniacid={$_W['uniacid']} and specid=$spec_id and id not in (" . implode(",", $itemids) . ")");
                    } else {
                        pdo_query("delete from " . tablename('trip_spec_item') . " where uniacid={$_W['uniacid']} and specid=$spec_id");
                    }

                    //更新规格项id
                    pdo_update("trip_spec", array("content" => serialize($itemids)), array("id" => $spec_id));

                    $specids[] = $spec_id;
                }

                //删除其他的
                if (count($specids) > 0) {
                    pdo_query("delete from " . tablename('trip_spec') . " where uniacid={$_W['uniacid']} and goodsid=$id and id not in (" . implode(",", $specids) . ")");
                } else {
                    pdo_query("delete from " . tablename('trip_spec') . " where uniacid={$_W['uniacid']} and goodsid=$id");
                }


                //保存规格

                $option_idss = $_POST['option_ids'];
                $option_productprices = $_POST['option_productprice'];
                $option_marketprices = $_POST['option_marketprice'];
                $option_costprices = $_POST['option_costprice'];
                $option_stocks = $_POST['option_stock'];
                $option_weights = $_POST['option_weight'];
                $len = count($option_idss);
                $optionids = array();
                for ($k = 0; $k < $len; $k++) {
                    $option_id = "";
                    $get_option_id = $_GPC['option_id_' . $ids][0];

                    $ids = $option_idss[$k];
                    $idsarr = explode("_", $ids);
                    $newids = array();
                    foreach ($idsarr as $key => $ida) {
                        foreach ($spec_items as $it) {
                            if ($it['get_id'] == $ida) {
                                $newids[] = $it['id'];
                                break;
                            }
                        }
                    }
                    $newids = implode("_", $newids);

                    $a = array(
                        "title" => $_GPC['option_title_' . $ids][0],
                        "productprice" => $_GPC['option_productprice_' . $ids][0],
                        "costprice" => $_GPC['option_costprice_' . $ids][0],
                        "marketprice" => $_GPC['option_marketprice_' . $ids][0],
                        "stock" => $_GPC['option_stock_' . $ids][0],
                        "weight" => $_GPC['option_weight_' . $ids][0],
                        "goodsid" => $id,
                        "specs" => $newids
                    );

                    $totalstocks += $a['stock'];

                    if (empty($get_option_id)) {
                        pdo_insert("trip_goods_option", $a);
                        $option_id = pdo_insertid();
                    } else {
                        pdo_update("trip_goods_option", $a, array('id' => $get_option_id));
                        $option_id = $get_option_id;
                    }
                    $optionids[] = $option_id;
                }
                if (count($optionids) > 0) {
                    pdo_query("delete from " . tablename('trip_goods_option') . " where goodsid=$id and id not in ( " . implode(',', $optionids) . ")");
                } else {
                    pdo_query("delete from " . tablename('trip_goods_option') . " where goodsid=$id");
                }


                //总库存
                if ($totalstocks > 0) {
                    pdo_update("trip_goods", array("total" => $totalstocks), array("id" => $id));
                }
                //message('商品更新成功！', $this->createWebUrl('goods', array('op' => 'display')), 'success');
                message('商品更新成功！', $this->createWebUrl('goods', array('op' => 'post', 'id' => $id)), 'success');
            }
        } elseif ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
            }

            if (!empty($_GPC['cate_2'])) {
                $cid = intval($_GPC['cate_2']);
                $condition .= " AND ccate = '{$cid}'";
            } elseif (!empty($_GPC['cate_1'])) {
                $cid = intval($_GPC['cate_1']);
                $condition .= " AND pcate = '{$cid}'";
            }

            if (isset($_GPC['status'])) {
                $condition .= " AND status = '" . intval($_GPC['status']) . "'";
            }
            
            $list = pdo_fetchall("SELECT * FROM " . tablename('trip_goods') . " WHERE uniacid = '{$_W['uniacid']}' and deleted=0 $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('trip_goods') . " WHERE uniacid = '{$_W['uniacid']}'  and deleted=0 $condition");
            $pager = pagination($total, $pindex, $psize);
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT id, thumb FROM " . tablename('trip_goods') . " WHERE id = :id", array(':id' => $id));
            if (empty($row)) {
                message('抱歉，商品不存在或是已经被删除！');
            }
//            if (!empty($row['thumb'])) {
//                file_delete($row['thumb']);
//            }
//            pdo_delete('trip_goods', array('id' => $id));
            //修改成不直接删除，而设置deleted=1
            pdo_update("trip_goods", array("deleted" => 1), array('id' => $id));

            message('删除成功！', referer(), 'success');
        } elseif ($operation == 'productdelete') {
            $id = intval($_GPC['id']);
            pdo_delete('trip_product', array('id' => $id));
            message('删除成功！', '', 'success');
        }
        include $this->template('goods');
    }

}
/*
$url = $this->mturl('index');
die('<script>location.href = "'.$url.'";</script>');
header("location:$url");
exit;
*/
/**
 * 生成分页数据
 * @param int $currentPage 当前页码
 * @param int $totalCount 总记录数
 * @param string $url 要生成的 url 格式，页码占位符请使用 *，如果未写占位符，系统将自动生成
 * @param int $pageSize 分页大小
 * @return string 分页HTML
 */
function pagination1($tcount, $pindex, $psize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => ''))
{
    global $_W;
    $pdata = array(
        'tcount' => 0,
        'tpage' => 0,
        'cindex' => 0,
        'findex' => 0,
        'pindex' => 0,
        'nindex' => 0,
        'lindex' => 0,
        'options' => ''
    );
    if ($context['ajaxcallback']) {
        $context['isajax'] = true;
    }

    $pdata['tcount'] = $tcount;
    $pdata['tpage'] = ceil($tcount / $psize);
    if ($pdata['tpage'] <= 1) {
        return '';
    }
    $cindex = $pindex;
    $cindex = min($cindex, $pdata['tpage']);
    $cindex = max($cindex, 1);
    $pdata['cindex'] = $cindex;
    $pdata['findex'] = 1;
    $pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
    $pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
    $pdata['lindex'] = $pdata['tpage'];

    if ($context['isajax']) {
        if (!$url) {
            $url = $_W['script_name'] . '?' . http_build_query($_GET);
        }
        $pdata['faa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['paa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['naa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', ' . $context['ajaxcallback'] . ')"';
        $pdata['laa'] = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', ' . $context['ajaxcallback'] . ')"';
    } else {
        if ($url) {
            $pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
            $pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
            $pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
            $pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
        } else {
            $_GET['page'] = $pdata['findex'];
            $pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['pindex'];
            $pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['nindex'];
            $pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
            $_GET['page'] = $pdata['lindex'];
            $pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
        }
    }

    $html = '<div class="pagination pagination-centered"><ul>';
    if ($pdata['cindex'] > 1) {
        $html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
        $html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
    }
    //页码算法：前5后4，不足10位补齐
    if (!$context['before'] && $context['before'] != 0) {
        $context['before'] = 5;
    }
    if (!$context['after'] && $context['after'] != 0) {
        $context['after'] = 4;
    }

    if ($context['after'] != 0 && $context['before'] != 0) {
        $range = array();
        $range['start'] = max(1, $pdata['cindex'] - $context['before']);
        $range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
        if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
            $range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
            $range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
        }
        for ($i = $range['start']; $i <= $range['end']; $i++) {
            if ($context['isajax']) {
                $aa = 'href="javascript:;" onclick="p(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', ' . $context['ajaxcallback'] . ')"';
            } else {
                if ($url) {
                    $aa = 'href="?' . str_replace('*', $i, $url) . '"';
                } else {
                    $_GET['page'] = $i;
                    $aa = 'href="?' . http_build_query($_GET) . '"';
                }
            }
            //$html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
        }
    }

    if ($pdata['cindex'] < $pdata['tpage']) {
        $html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
        $html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
    }
    $html .= '</ul></div>';
    return $html;
}

function haha($hehe)
{
    $phone = $hehe;
    $mphone = substr($phone, 3, 6);
    $lphone = str_replace($mphone, "****", $phone);
    return $lphone;
}


function hehe($string = null)
{
    // 将字符串分解为单元
    $name = $string;
    preg_match_all("/./us", $string, $match);
    if (count($match[0]) > 7) {
        $mname = '';
        for ($i = 0; $i < 7; $i++) {
            $mname = $mname . $match[0][$i];
        }
        $name = $mname . '..';
    }
    return $name;
}


function img_url($img = '')
{
    global $_W;
    if (empty($img)) {
        return "";
    }
    if (substr($img, 0, 6) == 'avatar') {
        return $_W['siteroot'] . "resource/image/avatar/" . $img;
    }
    if (substr($img, 0, 8) == './themes') {
        return $_W['siteroot'] . $img;
    }
    if (substr($img, 0, 1) == '.') {
        return $_W['siteroot'] . substr($img, 2);
    }
    if (substr($img, 0, 5) == 'http:') {
        return $img;
    }
    return $_W['attachurl'] . $img;
}


