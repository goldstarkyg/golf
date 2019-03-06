<?php

defined('IN_IA') or exit('Access Denied');

session_start();

class GolfModuleSite extends WeModuleSite
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
                    pdo_update('golf_category', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
            }
            $children = array();
            $category = pdo_fetchall("SELECT * FROM " . tablename('golf_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder DESC");
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
                $category = pdo_fetch("SELECT * FROM " . tablename('golf_category') . " WHERE id = '$id'");
            } else {
                $category = array(
                    'displayorder' => 0,
                );
            }
            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, name FROM " . tablename('golf_category') . " WHERE id = '$parentid'");
                if (empty($parent)) {
                    message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
                }
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['catename'])) {
                    message('抱歉，请输入分类名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['catename'],
                    'enabled'       => intval($_GPC['enabled']),
                    'displayorder'  => intval($_GPC['displayorder']),
                    'isrecommand'   => intval($_GPC['isrecommand']),
                    //    'commission' => intval($_GPC['commission']),
                    'description'   => $_GPC['description'],
                    'type'          => intval($_GPC['type']),
                    'type_value'    => intval($_GPC['type_value']),
                    'parentid' => intval($parentid),
                );
                if (!empty($_GPC['thumb'])) {
                    $data['thumb'] = $_GPC['thumb'];
                } else {
                    $data['thumb'] = '';
                }
                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update('golf_category', $data, array('id' => $id));
                } else {
                    pdo_insert('golf_category', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
            }
            include $this->template('category');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $category = pdo_fetch("SELECT id, parentid FROM " . tablename('golf_category') . " WHERE id = '$id'");
            if (empty($category)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('category', array('op' => 'display')), 'error');
            }
            pdo_delete('golf_category', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
        }


    }

    public function doWebGiftCategory()
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
                    pdo_update_table('gift_category', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('giftcategory', array('op' => 'display')), 'success');
            }
            $children = array();
            $category = pdo_fetchall("SELECT * FROM gift_category WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder DESC");
            foreach ($category as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($category[$index]);
                }
            }
            include $this->template('giftcategory');
        } elseif ($operation == 'post') {
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $category = pdo_fetch("SELECT * FROM gift_category WHERE id = '$id'");
            } else {
                $category = array(
                    'displayorder' => 0,
                );
            }
            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, name FROM gift_category WHERE id = '$parentid'");
                if (empty($parent)) {
                    message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
                }
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['catename'])) {
                    message('抱歉，请输入分类名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['catename'],
                    'enabled'       => intval($_GPC['enabled']),
                    'displayorder'  => intval($_GPC['displayorder']),
                    'isrecommand'   => intval($_GPC['isrecommand']),
                    //    'commission' => intval($_GPC['commission']),
                    'description'   => $_GPC['description'],
                    'parentid' => intval($parentid),
                );
                if (!empty($_GPC['thumb'])) {
                    $data['thumb'] = $_GPC['thumb'];
                } else {
                    $data['thumb'] = '';
                }
                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update_table('gift_category', $data, array('id' => $id));
                } else {
                    pdo_insert_table('gift_category', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('giftcategory', array('op' => 'display')), 'success');
            }
            include $this->template('giftcategory');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $category = pdo_fetch("SELECT id, parentid FROM gift_category WHERE id = '$id'");
            if (empty($category)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('giftcategory', array('op' => 'display')), 'error');
            }
            pdo_delete_table('gift_category', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('giftcategory', array('op' => 'display')), 'success');
        }
    }

    public function doWebVideotag()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update_table('video_tag', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('videotag', array('op' => 'display')), 'success');
            }
            $videotag = pdo_fetchall("SELECT * FROM video_tag ORDER BY displayorder DESC");
            include $this->template('videotag');
        } elseif ($operation == 'post') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $videotag = pdo_fetch("SELECT * FROM video_tag WHERE id = '$id'");
            } else {
                $videotag = array(
                    'displayorder' => 0,
                );
            }

            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('抱歉，请输入分类名称！');
                }
                $data = array(
                    //'uniacid'       => $_W['uniacid'],
                    'title'          => $_GPC['title'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'isrecommend'   => intval($_GPC['isrecommend']),
                    'ishot'         => intval($_GPC['ishot'])
                );

                if (!empty($id)) {
                    pdo_update_table('video_tag', $data, array('id' => $id));
                } else {
                    pdo_insert_table('video_tag', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('videotag', array('op' => 'display')), 'success');
            }
            include $this->template('videotag');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $videotag = pdo_fetch("SELECT id FROM video_tag WHERE id = '$id'");
            if (empty($videotag)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('videotag', array('op' => 'display')), 'error');
            }
            pdo_delete_table('video_tag', array('id' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('videotag', array('op' => 'display')), 'success');
        }
    }

    public function doWebBanner()
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
                    pdo_update('golf_banner', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('banner', array('op' => 'display')), 'success');
            }
            $children = array();
            $banner = pdo_fetchall("SELECT * FROM " . tablename('golf_banner') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder DESC");
            foreach ($banner as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($banner[$index]);
                }
            }
            include $this->template('banner');
        } elseif ($operation == 'post') {
            load()->func('file');
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $banner = pdo_fetch("SELECT * FROM " . tablename('golf_banner') . " WHERE id = '$id'");
            } else {
                $banner = array(
                    'displayorder' => 0,
                );
            }
            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, name FROM " . tablename('golf_banner') . " WHERE id = '$parentid'");
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

//                if (!empty($_FILES['thumb']['tmp_name'])) {
//                    file_delete($_GPC['thumb_old']);
//                    $upload = file_upload($_FILES['thumb']);
//                    if (is_error($upload)) {
//                        message($upload['message'], '', 'error');
//                    }
//                    $data['thumb'] = $upload['path'];
//                }

                if (!empty($_GPC['thumb'])) {
                    $data['thumb'] = $_GPC['thumb'];
                } else {
                    $data['thumb'] = '';
                }

                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update('golf_banner', $data, array('id' => $id));
                } else {
                    pdo_insert('golf_banner', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('banner', array('op' => 'display')), 'success');
            }
            include $this->template('banner');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $banner = pdo_fetch("SELECT id, parentid FROM " . tablename('golf_banner') . " WHERE id = '$id'");
            if (empty($banner)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('banner', array('op' => 'display')), 'error');
            }
            pdo_delete('golf_banner', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('banner', array('op' => 'display')), 'success');
        }

    }

    public function doWebAdv()
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
                    pdo_update('golf_adv', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
            }
            $children = array();
            $adv = pdo_fetchall("SELECT * FROM " . tablename('golf_adv') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder DESC");
            foreach ($adv as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($adv[$index]);
                }
            }
            include $this->template('adv');
        } elseif ($operation == 'post') {
            load()->func('file');
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $adv = pdo_fetch("SELECT * FROM " . tablename('golf_adv') . " WHERE id = '$id'");
            } else {
                $adv = array(
                    'displayorder' => 0,
                );
            }
            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, name FROM " . tablename('golf_adv') . " WHERE id = '$parentid'");
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

//                if (!empty($_FILES['thumb']['tmp_name'])) {
//                    file_delete($_GPC['thumb_old']);
//                    $upload = file_upload($_FILES['thumb']);
//                    if (is_error($upload)) {
//                        message($upload['message'], '', 'error');
//                    }
//                    $data['thumb'] = $upload['path'];
//                }

                if (!empty($_GPC['thumb'])) {
                    $data['thumb'] = $_GPC['thumb'];
                } else {
                    $data['thumb'] = '';
                }

                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update('golf_adv', $data, array('id' => $id));
                } else {
                    pdo_insert('golf_adv', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
            }
            include $this->template('adv');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $adv = pdo_fetch("SELECT id, parentid FROM " . tablename('golf_adv') . " WHERE id = '$id'");
            if (empty($adv)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('adv', array('op' => 'display')), 'error');
            }
            pdo_delete('golf_adv', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
        }

    }

    public function doWebArticle()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        $category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('golf_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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

        if ($operation == 'display') {
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

            $list = pdo_fetchall("SELECT * FROM " . tablename('golf_article') . " WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('golf_article') . " WHERE uniacid = '{$_W['uniacid']}'");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('article');
        } elseif ($operation == 'post') {
            load()->func('file');
            $id = intval($_GPC['id']);
            $template = uni_templates();
            $pcate = $_GPC['pcate'];
            $ccate = $_GPC['ccate'];
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('golf_article') . " WHERE id = :id", array(':id' => $id));
                $item['type'] = explode(',', $item['type']);
                $pcate = $item['pcate'];
                $ccate = $item['ccate'];
                if (empty($item)) {
                    message('抱歉，文章不存在或是已经删除！', '', 'error');
                }
                $key = pdo_fetchall('SELECT content FROM ' . tablename('rule_keyword') . ' WHERE rid = :rid AND uniacid = :uniacid', array(':rid' => $item['rid'], ':uniacid' => $_W['uniacid']));
                if (!empty($key)) {
                    $keywords = array();
                    foreach ($key as $row) {
                        $keywords[] = $row['content'];
                    }
                    $keywords = implode(',', array_values($keywords));
                }
                $item['credit'] = iunserializer($item['credit']) ? iunserializer($item['credit']) : array();
                if (!empty($item['credit']['limit'])) {
                    $credit_num = pdo_fetchcolumn('SELECT SUM(credit_value) FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND module = :module AND sign = :sign', array(':uniacid' => $_W['uniacid'], ':module' => 'article', ':sign' => md5(iserializer(array('id' => $id)))));
                    if (is_null($credit_num)) $credit_num = 0;
                    $credit_yu = (($item['credit']['limit'] - $credit_num) < 0) ? 0 : $item['credit']['limit'] - $credit_num;
                }
            } else {
                $item['credit'] = array();
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('标题不能为空，请输入标题！');
                }
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'iscommend' => intval($_GPC['option']['commend']),
                    'ishot' => intval($_GPC['option']['hot']),
                    'isnew' => intval($_GPC['option']['new']),
                    'pcate' => intval($_GPC['category']['parentid']),
                    'ccate' => intval($_GPC['category']['childid']),
                    'template' => $_GPC['template'],
                    'title' => $_GPC['title'],
                    'description' => $_GPC['description'],
                    'content' => htmlspecialchars_decode($_GPC['content'], ENT_QUOTES),
                    'incontent' => intval($_GPC['incontent']),
                    'source' => $_GPC['source'],
                    'author' => $_GPC['author'],
                    'displayorder' => intval($_GPC['displayorder']),
                    'linkurl' => $_GPC['linkurl'],
                    'createtime' => TIMESTAMP,
                    'click' => intval($_GPC['click'])
                );
                if (!empty($_GPC['thumb'])) {
                    $data['thumb'] = $_GPC['thumb'];
                } elseif (!empty($_GPC['autolitpic'])) {
                    $match = array();
                    preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $_GPC['content'], $match);
                    if (!empty($match[1])) {
                        $data['thumb'] = $match[1] . $match[2];
                    }
                } else {
                    $data['thumb'] = '';
                }
                $keyword = str_replace('，', ',', trim($_GPC['keyword']));
                $keyword = explode(',', $keyword);
                if (!empty($keyword)) {
                    $rule['uniacid'] = $_W['uniacid'];
                    $rule['name'] = '文章：' . $_GPC['title'] . ' 触发规则';
                    $rule['module'] = 'news';
                    $rule['status'] = 1;
                    $keywords = array();
                    foreach ($keyword as $key) {
                        $key = trim($key);
                        if (empty($key)) continue;
                        $keywords[] = array(
                            'uniacid' => $_W['uniacid'],
                            'module' => 'news',
                            'content' => $key,
                            'status' => 1,
                            'type' => 1,
                            'displayorder' => 1,
                        );
                    }
                    $reply['title'] = $_GPC['title'];
                    $reply['description'] = $_GPC['description'];
                    $reply['thumb'] = $_GPC['thumb'];
                    $reply['url'] = murl('site/site/detail', array('id' => $id));
                }
                if (!empty($_GPC['credit']['status'])) {
                    $credit['status'] = intval($_GPC['credit']['status']);
                    $credit['limit'] = intval($_GPC['credit']['limit']) ? intval($_GPC['credit']['limit']) : message('请设置积分上限');
                    $credit['share'] = intval($_GPC['credit']['share']) ? intval($_GPC['credit']['share']) : message('请设置分享时赠送积分多少');
                    $credit['click'] = intval($_GPC['credit']['click']) ? intval($_GPC['credit']['click']) : message('请设置阅读时赠送积分多少');
                    $data['credit'] = iserializer($credit);
                } else {
                    $data['credit'] = iserializer(array('status' => 0, 'limit' => 0, 'share' => 0, 'click' => 0));
                }
                if (empty($id)) {
                    if (!empty($keywords)) {
                        pdo_insert('rule', $rule);
                        $rid = pdo_insertid();
                        foreach ($keywords as $li) {
                            $li['rid'] = $rid;
                            pdo_insert('rule_keyword', $li);
                        }
                        $reply['rid'] = $rid;
                        pdo_insert('news_reply', $reply);
                        $data['rid'] = $rid;
                    }
                    pdo_insert('golf_article', $data);
                    $aid = pdo_insertid();
                    pdo_update('news_reply', array('url' => murl('site/site/detail', array('id' => $aid))), array('rid' => $rid));
                } else {
                    unset($data['createtime']);
                    pdo_delete('rule', array('id' => $item['rid'], 'uniacid' => $_W['uniacid']));
                    pdo_delete('rule_keyword', array('rid' => $item['rid'], 'uniacid' => $_W['uniacid']));
                    pdo_delete('news_reply', array('rid' => $item['rid']));
                    if (!empty($keywords)) {
                        pdo_insert('rule', $rule);
                        $rid = pdo_insertid();

                        foreach ($keywords as $li) {
                            $li['rid'] = $rid;
                            pdo_insert('rule_keyword', $li);
                        }

                        $reply['rid'] = $rid;
                        pdo_insert('news_reply', $reply);
                        $data['rid'] = $rid;
                    } else {
                        $data['rid'] = 0;
                        $data['kid'] = 0;
                    }
                    pdo_update('golf_article', $data, array('id' => $id));
                }
                message('文章更新成功！', $this->createWebUrl('article', array('op' => 'display')), 'success');
            } else {
                include $this->template('article');
            }
        } elseif ($operation == 'delete') {
            load()->func('file');
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT id,rid,kid,thumb FROM " . tablename('golf_article') . " WHERE id = :id", array(':id' => $id));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            if (!empty($row['thumb'])) {
                file_delete($row['thumb']);
            }
            if (!empty($row['rid'])) {
                pdo_delete('rule', array('id' => $row['rid'], 'uniacid' => $_W['uniacid']));
                pdo_delete('rule_keyword', array('rid' => $row['rid'], 'uniacid' => $_W['uniacid']));
                pdo_delete('news_reply', array('rid' => $row['rid']));
            }
            pdo_delete('golf_article', array('id' => $id));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doWebGoodList()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        $category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('golf_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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

        $good_parent = array();
        $good_children = array();
        $good_category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('eso_sale_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder DESC", array(), 'id');
        if (!empty($good_category)) {
            $good_children = '';
            foreach ($good_category as $cid => $cate) {
                if (!empty($cate['parentid'])) {
                    $good_children[$cate['parentid']][$cate['id']] = $cate;
                }else {
                    $good_parent[$cate['id']] = $cate;
                }
            }
        }
        if($operation == 'display_good') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
            }

            $golf_category_id = intval($_GPC['category']['parentid']);

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
            if(!empty($golf_category_id)) {
                $list = pdo_fetchall("SELECT * FROM " . tablename('eso_sale_goods') . " WHERE uniacid = '{$_W['uniacid']}' and deleted=0 $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
                $count = 0;
                foreach ($list as $li) {
                    $good_id = $li[id];
                    $video_good = pdo_fetch("SELECT * FROM video_good_list WHERE good_id ='" . $good_id . "' and uniacid = '{$_W['uniacid']}' and category_id='" . $golf_category_id . "'");
                    if (!empty($video_good)) {
                        $list[$count]['good_status'] = 1;
                    } else {
                        $list[$count]['good_status'] = 0;
                    }
                    $count++;
                }
                $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('eso_sale_goods') . " WHERE uniacid = '{$_W['uniacid']}'  and deleted=0 $condition");
                $pager = pagination($total, $pindex, $psize);
            }
            include $this->template('goodlist');
        } elseif ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $params = array();
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND eg.title LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            if (!empty($_GPC['category']['childid'])) {
                //$cid = intval($_GPC['category']['childid']);
                //$condition .= " AND ccate = '{$cid}'";
            } elseif (!empty($_GPC['category']['parentid'])) {
                $cid = intval($_GPC['category']['parentid']);
                $condition .= " AND vg.category_id = '{$cid}'";
            }
            $sql = "SELECT eg.*,vg.category_id FROM video_good_list as vg left join ims_eso_sale_goods as eg ON vg.good_id = eg.id   WHERE eg.uniacid = '".$uniacid."' ".$condition." ORDER BY eg.displayorder DESC, eg.id DESC LIMIT " . ($pindex - 1) * $psize . ", " . $psize;
            $list = pdo_fetchall($sql, $params);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM video_good_list as vg left join ims_eso_sale_goods as eg ON vg.good_id = eg.id   WHERE eg.uniacid = '".$uniacid."' ".$condition);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('goodlist');
        } elseif ($operation == 'post') {
            load()->func('file');
            $category_id = intval($_GPC['post_category_id']);
            $good_status = $_GPC['good_status'];
            pdo_delete_table('video_good_list', array('category_id' => $category_id,'uniacid'=>$uniacid));
            foreach ($good_status as $go) {
                $data = array('uniacid' => $uniacid, 'category_id' => $category_id , 'good_id'=>$go );
                pdo_insert_table('video_good_list', $data);
            }
            $operation = 'display';
            include $this->template('goodlist');
        } elseif ($operation == 'delete') {
            load()->func('file');
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT * FROM video_good_list WHERE id = :id", array(':id' => $id));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            pdo_delete_table('video_good_list', array('id' => $id));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doWebGiftList()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        $category = pdo_fetchall("SELECT id,parentid,name FROM gift_category WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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

        if ($operation == 'display') {
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

            $list = pdo_fetchall("SELECT * FROM gift_list WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn(" SELECT COUNT(*) FROM gift_list WHERE uniacid = '{$_W['uniacid']}'");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('giftlist');
        } elseif ($operation == 'post') {
            load()->func('file');
            $id = intval($_GPC['id']);
            $template = uni_templates();
            $pcate = $_GPC['pcate'];
            $ccate = $_GPC['ccate'];
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM gift_list WHERE id = :id", array(':id' => $id));
                $pcate = $item['pcate'];
                $ccate = $item['ccate'];
                if (empty($item)) {
                    message('抱歉，文章不存在或是已经删除！', '', 'error');
                }
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('标题不能为空，请输入标题！');
                }
                $max_size = $_GPC['max_size'];
                $stock = $_GPC['stock'];
                if($max_size == '1') {
                    if($stock > 1)  message("Can 't enter bigger than 1.");
                }
                if($max_size == '10') {
                    if($stock > 10)  message("Can 't enter bigger than 10.");
                }
                if($max_size == '100') {
                    if($stock > 100)  message("Can 't enter bigger than 100.");
                }
                if($max_size == '1000') {
                    if($stock > 1000)  message("Can 't enter bigger than 1000.");
                }
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'iscommend' => intval($_GPC['option']['commend']),
                    'ishot' => intval($_GPC['option']['hot']),
                    'isnew' => intval($_GPC['option']['new']),
                    'pcate' => intval($_GPC['category']['parentid']),
                    'ccate' => intval($_GPC['category']['childid']),
                    'title' => $_GPC['title'],
                    'description' => $_GPC['description'],
                    'displayorder' => intval($_GPC['displayorder']),
                    'price' => $_GPC['price'],
                    'max_size' => $_GPC['max_size'],
                    'stock' => $_GPC['stock'],
                );
                if (!empty($_GPC['thumb'])) {
                    $data['thumb'] = $_GPC['thumb'];
                } elseif (!empty($_GPC['autolitpic'])) {
                    $match = array();
                    preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $_GPC['content'], $match);
                    if (!empty($match[1])) {
                        $data['thumb'] = $match[1] . $match[2];
                    }
                } else {
                    $data['thumb'] = '';
                }
                if (empty($id)) {
                    pdo_insert_table('gift_list', $data);
                } else {
                    unset($data['createtime']);
                    pdo_update_table('gift_list', $data, array('id' => $id));
                }
                message('文章更新成功！', $this->createWebUrl('giftlist', array('op' => 'display')), 'success');
            } else {
                include $this->template('giftlist');
            }
        } elseif ($operation == 'delete') {
            load()->func('file');
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT * FROM gift_list WHERE id = :id", array(':id' => $id));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            if (!empty($row['thumb'])) {
                file_delete($row['thumb']);
            }
            pdo_delete_table('gift_list', array('id' => $id));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doWebFanList()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        if ($operation == 'display') {
            $list = pdo_fetchall("SELECT * FROM fan_list WHERE uniacid = '".$uniacid."' ORDER BY displayorder DESC ");
            include $this->template('fanlist');
        } elseif ($operation == 'post') {
            load()->func('file');
            $id = intval($_GPC['id']);
            $template = uni_templates();
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM fan_list WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，文章不存在或是已经删除！', '', 'error');
                }
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('标题不能为空，请输入标题！');
                }
                $data = array(
                    'uniacid' => $uniacid,
                    'iscommend' => intval($_GPC['option']['commend']),
                    'title' => $_GPC['title'],
                    'period' => intval($_GPC['period']),
                    'price' => $_GPC['price'],
                    'displayorder' => $_GPC['displayorder'],
                );
                if (empty($id)) {
                    pdo_insert_table('fan_list', $data);
                } else {
                    pdo_update_table('fan_list', $data, array('id' => $id));
                }
                message('文章更新成功！', $this->createWebUrl('fanlist', array('op' => 'display')), 'success');
            } else {
                include $this->template('fanlist');
            }
        } elseif ($operation == 'delete') {
            load()->func('file');
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT * FROM fan_list WHERE id = :id", array(':id' => $id));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            pdo_delete_table('fan_list', array('id' => $id));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doWebLivevideo()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        $category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('golf_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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

        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $params = array();
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND li.title LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            if (!empty($_GPC['category']['childid'])) {
                $cid = intval($_GPC['category']['childid']);
                $condition .= " AND me.ccate = '{$cid}'";
            } elseif (!empty($_GPC['category']['parentid'])) {
                $cid = intval($_GPC['category']['parentid']);
                $condition .= " AND me.pcate = '{$cid}'";
            }

            $list = pdo_fetchall("SELECT * FROM live_data li join ".tablename('mc_members')." me on li.userid = me.tencentuserid WHERE me.uniacid = '{$_W['uniacid']}' $condition ORDER BY li.viewer_count DESC, li.viewer_count DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $count = 0;
            foreach ($list as $li) {
                $list[$count]['count'] = $count;
                $host_userid = $li['userid'];
                $comment = pdo_fetchall("SELECT * FROM video_comment WHERE uniacid = '{$_W['uniacid']}' and host_userid='".$host_userid."' ORDER BY created_at DESC ");
                $list[$count]['comment'] = $comment;
                $count++;
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM live_data li join ".tablename('mc_members')." me on li.userid = me.tencentuserid WHERE me.uniacid = '{$_W['uniacid']}' $condition ");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('livevideo');
        } elseif ($operation == 'post') {
            load()->func('file');
            $userid = $_GPC['userid'];
            $template = uni_templates();
            $pcate = $_GPC['pcate'];
            $ccate = $_GPC['ccate'];
            if (!empty($userid)) {
                $item = pdo_fetch("SELECT * FROM live_data WHERE userid = :userid", array(':userid' =>$userid));
                $pcate = $item['pcate'];
                $ccate = $item['ccate'];
                if (empty($item)) {
                    message('抱歉，文章不存在或是已经删除！', '', 'error');
                }
            }
            //get video list
            $videotags  = pdo_fetchall("SELECT * FROM video_tag WHERE isrecommend = '1' ORDER By displayorder ASC, id ASC ");
            $selected_video = array();
            foreach ($videotags as $tag) {
                $video_selected = pdo_fetch("SELECT * FROM video_selected WHERE video_id like '" . $userid . "' and tag_id='" . $tag['id'] . "' and video_type='live' ");
                $tag['userid'] = $userid;
                if (!empty($video_selected)) $tag['selected_video'] = '1';
                else $tag['selected_video'] = '0';
                array_push($selected_video, $tag);
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('标题不能为空，请输入标题！');
                }
                $tag_list = $_GPC['ids'];
                pdo_delete_table('video_selected', array('video_id' => $userid));
                if(!empty($tag_list)) {
                    foreach ($tag_list as $ta) {
                        $li = explode("_", $ta);
                        $video = array();
                        $video['video_id'] = $li[0];
                        $video['tag_id'] = $li[1];
                        $video['video_type'] = 'live';
                        pdo_insert_table('video_selected', $video);
                    }
                }
                $data = array(
                    //'uniacid' => $_W['uniacid'],
                    'iscommend' => intval($_GPC['option']['commend']),
                    'ishot' => intval($_GPC['option']['hot']),
                    'isnew' => intval($_GPC['option']['new']),
                    'title' => $_GPC['title'],
                    'desc' => $_GPC['desc'],
                    'viewer_count' => $_GPC['viewer_count'],
                    'like_count' => $_GPC['like_count']
                );
                //get video tags list
                unset($data['createtime']);
                pdo_update_table('live_data', $data, array('userid'=>$userid));
                message('文章更新成功！', $this->createWebUrl('livevideo', array('op' => 'display')), 'success');
            } else {
                include $this->template('livevideo');
            }
        } elseif ($operation == 'delete') {
            load()->func('file');
            $userid = $_GPC['userid'];
            $row = pdo_fetch("SELECT * FROM live_date WHERE userid = :userid", array(':userid' => $userid));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            pdo_delete_table('live_data', array('userid' => $userid));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doWebSetLiveVideoProperty()
    {

        global $_GPC, $_W;

        $userid = intval($_GPC['userid']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        empty($data) ? ($data = 1) : $data = 0;
        if (!in_array($type, array('new', 'hot', 'commend'))) {
            die(json_encode(array("result" => 0)));
        }
        if ($_GPC['type'] == 'status') {
            //pdo_update("live_data", array($type => $data), array("userid" => $userid, "uniacid" => $_W['uniacid']));
        } else {
            pdo_update_table("live_data", array("is" . $type => $data), array("userid" => $userid ));
        }
        die(json_encode(array("result" => 1, "data" => $data)));
    }

    public function doWebSetUgcVideoProperty()
    {

        global $_GPC, $_W;

        $file_id = $_GPC['file_id'];
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        empty($data) ? ($data = 1) : $data = 0;
        if (!in_array($type, array('new', 'hot', 'commend'))) {
            die(json_encode(array("result" => 0)));
        }
        if ($_GPC['type'] == 'status') {
            //pdo_update("live_data", array($type => $data), array("userid" => $userid, "uniacid" => $_W['uniacid']));
        } else {
            pdo_update_table("UGC_data", array("is" . $type => $data), array("fiel_id" => $file_id ));
        }
        die(json_encode(array("result" => 1, "data" => $data)));
    }

    public function doWebSetVodVideoProperty()
    {

        global $_GPC, $_W;

        $file_id = $_GPC['file_id'];
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        empty($data) ? ($data = 1) : $data = 0;
        if (!in_array($type, array('new', 'hot', 'commend'))) {
            die(json_encode(array("result" => 0)));
        }
        if ($_GPC['type'] == 'status') {
            //pdo_update("live_data", array($type => $data), array("userid" => $userid, "uniacid" => $_W['uniacid']));
        } else {
            pdo_update_table("VOD_data", array("is" . $type => $data), array("file_id" => $file_id ));
        }
        die(json_encode(array("result" => 1, "data" => $data)));
    }

    public function doWebUgcvideo()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        $category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('golf_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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

        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $params = array();
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND li.title LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            if (!empty($_GPC['category']['childid'])) {
                $cid = intval($_GPC['category']['childid']);
                $condition .= " AND me.ccate = '{$cid}'";
            } elseif (!empty($_GPC['category']['parentid'])) {
                $cid = intval($_GPC['category']['parentid']);
                $condition .= " AND me.pcate = '{$cid}'";
            }

            $list = pdo_fetchall("SELECT * FROM UGC_data li join ".tablename('mc_members')." me on li.userid = me.tencentuserid WHERE me.uniacid = '{$_W['uniacid']}' $condition ORDER BY li.viewer_count DESC, li.viewer_count DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM UGC_data li join ".tablename('mc_members')." me on li.userid = me.tencentuserid WHERE me.uniacid = '{$_W['uniacid']}' $condition ");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('ugcvideo');
        } elseif ($operation == 'post') {
            load()->func('file');
            $userid = $_GPC['userid'];
            $file_id = $_GPC['file_id'];
            $template = uni_templates();
            $pcate = $_GPC['pcate'];
            $ccate = $_GPC['ccate'];
            if (!empty($file_id)) {
                $item = pdo_fetch("SELECT * FROM UGC_data WHERE file_id = :file_id", array(':file_id' =>$file_id));
                $pcate = $item['pcate'];
                $ccate = $item['ccate'];
                if (empty($item)) {
                    message('抱歉，文章不存在或是已经删除！', '', 'error');
                }
            }
            //get video list
            $videotags  = pdo_fetchall("SELECT * FROM video_tag WHERE isrecommend = '1' ORDER By displayorder ASC, id ASC ");
            $selected_video = array();
            foreach ($videotags as $tag) {
                $video_selected = pdo_fetch("SELECT * FROM video_selected WHERE video_id like '".$file_id."' and tag_id='".$tag['id']."' and video_type='ugc' ");
                $tag['file_id'] = $file_id;
                if(!empty($video_selected)) $tag['selected_video'] = '1';
                else $tag['selected_video'] = '0';
                array_push($selected_video, $tag);
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('标题不能为空，请输入标题！');
                }
                $tag_list = $_GPC['ids'];
                pdo_delete_table('video_selected', array('video_id' => $file_id));
                if(!empty($tag_list)) {
                    foreach ($tag_list as $ta) {
                        $li = explode("_", $ta);
                        $video = array();
                        $video['video_id'] = $li[0];
                        $video['tag_id'] = $li[1];
                        $video['video_type'] = 'ugc';
                        pdo_insert_table('video_selected', $video);
                    }
                }
                $data = array(
                    //'uniacid' => $_W['uniacid'],
                    'iscommend' => intval($_GPC['option']['commend']),
                    'ishot' => intval($_GPC['option']['hot']),
                    'isnew' => intval($_GPC['option']['new']),
                    'title' => $_GPC['title'],
                    'desc' => $_GPC['desc'],
                    'viewer_count' => $_GPC['viewer_count'],
                    'like_count' => $_GPC['like_count']
                );

                unset($data['createtime']);
                pdo_update_table('UGC_data', $data, array('file_id'=>$file_id));
                message('文章更新成功！', $this->createWebUrl('ugcvideo', array('op' => 'display')), 'success');
            } else {
                include $this->template('ugcvideo');
            }
        } elseif ($operation == 'delete') {
            load()->func('file');
            $file_id = $_GPC['file_id'];
            $row = pdo_fetch("SELECT * FROM UGC_data WHERE file_id = :file_id", array(':file_id' => $file_id));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            pdo_delete_table('UGC_data', array('file_id' => $file_id));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doWebVodvideo()
    {
        global $_W, $_GPC;

        checklogin();
        load()->func('tpl');
        $uniacid = $_W['uniacid'];
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        $category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('golf_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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

        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $params = array();
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND li.title LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            if (!empty($_GPC['category']['childid'])) {
                $cid = intval($_GPC['category']['childid']);
                $condition .= " AND me.ccate = '{$cid}'";
            } elseif (!empty($_GPC['category']['parentid'])) {
                $cid = intval($_GPC['category']['parentid']);
                $condition .= " AND me.pcate = '{$cid}'";
            }

            $list = pdo_fetchall("SELECT * FROM VOD_data li join ".tablename('mc_members')." me on li.userid = me.tencentuserid WHERE me.uniacid = '{$_W['uniacid']}' $condition ORDER BY li.viewer_count DESC, li.viewer_count DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM VOD_data li join ".tablename('mc_members')." me on li.userid = me.tencentuserid WHERE me.uniacid = '{$_W['uniacid']}' $condition ");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('vodvideo');
        } elseif ($operation == 'post') {
            load()->func('file');
            $userid = $_GPC['userid'];
            $file_id = $_GPC['file_id'];
            $template = uni_templates();
            $pcate = $_GPC['pcate'];
            $ccate = $_GPC['ccate'];
            if (!empty($userid)) {
                $item = pdo_fetch("SELECT * FROM VOD_data WHERE file_id = :file_id", array(':file_id' =>$file_id));
                $pcate = $item['pcate'];
                $ccate = $item['ccate'];
                if (empty($item)) {
                    message('抱歉，文章不存在或是已经删除！', '', 'error');
                }
            }
            //get video list
            $videotags  = pdo_fetchall("SELECT * FROM video_tag WHERE isrecommend = '1' ORDER By displayorder ASC, id ASC ");
            $selected_video = array();
            foreach ($videotags as $tag) {
                $video_selected = pdo_fetch("SELECT * FROM video_selected WHERE video_id like '".$file_id."' and tag_id='".$tag['id']."' and video_type='vod' ");
                $tag['file_id'] = $file_id;
                if(!empty($video_selected)) $tag['selected_video'] = '1';
                else $tag['selected_video'] = '0';
                array_push($selected_video, $tag);
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('标题不能为空，请输入标题！');
                }
                $tag_list = $_GPC['ids'];
                pdo_delete_table('video_selected', array('video_id' => $file_id));
                if(!empty($tag_list)) {
                    foreach ($tag_list as $ta) {
                        $li = explode("_", $ta);
                        $video = array();
                        $video['video_id'] = $li[0];
                        $video['tag_id'] = $li[1];
                        $video['video_type'] = 'vod';
                        pdo_insert_table('video_selected', $video);
                    }
                }
                $data = array(
                    //'uniacid' => $_W['uniacid'],
                    'iscommend' => intval($_GPC['option']['commend']),
                    'ishot' => intval($_GPC['option']['hot']),
                    'isnew' => intval($_GPC['option']['new']),
                    'title' => $_GPC['title'],
                    'desc' => $_GPC['desc'],
                    'viewer_count' => $_GPC['viewer_count'],
                    'like_count' => $_GPC['like_count']
                );
                unset($data['createtime']);
                pdo_update_table('VOD_data', $data, array('file_id'=>$file_id));
                message('文章更新成功！', $this->createWebUrl('vodvideo', array('op' => 'display')), 'success');
            } else {
                include $this->template('vodvideo');
            }
        } elseif ($operation == 'delete') {
            load()->func('file');
            $file_id = $_GPC['file_id'];
            $row = pdo_fetch("SELECT * FROM VOD_data WHERE file_id = :file_id", array(':file_id' => $file_id));
            if (empty($row)) {
                message('抱歉，文章不存在或是已经被删除！');
            }
            pdo_delete_table('VOD_data', array('file_id' => $file_id));
            message('删除成功！', referer(), 'success');
        }
    }

    public function doWebMember()
    {
        global $_GPC, $_W;
        checklogin();
        load()->func('tpl');
        load()->model('mc');
        $op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        $category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('golf_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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

        if($operation == 'display') {
            $_W['page']['title'] = '会员列表 - 会员 - 会员中心';
            $groups = mc_groups();
            $pindex = max(1, intval($_GPC['page']));
            $psize = 50;
            $condition = '';
            $starttime = empty($_GPC['createtime']['start']) ? strtotime('-90 days') : strtotime($_GPC['createtime']['start']);
            $endtime = empty($_GPC['createtime']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['createtime']['end']) + 86399;
            $condition .= " AND createtime >= {$starttime} AND createtime <= {$endtime}";
            $condition .= empty($_GPC['username']) ? '' : " AND (( `realname` LIKE '%".trim($_GPC['username'])."%' ) OR ( `nickname` LIKE '%".trim($_GPC['username'])."%' ) OR ( `mobile` LIKE '%".trim($_GPC['username'])."%' )";
            if (!empty($_GPC['username'])) {
                if (strlen(trim($_GPC['username'])) == 28) {
                    $condition .= " OR ( `uid` = (SELECT `uid` FROM". tablename('mc_mapping_fans')." WHERE openid = '".trim($_GPC['username'])."')))";
                } else {
                    $condition .= ")";
                }
            }
            $condition .= intval($_GPC['groupid']) > 0 ?  " AND `groupid` = '".intval($_GPC['groupid'])."'" : '';
            if(checksubmit('export_submit', true)) {
                $sql = "SELECT uid, uniacid, groupid, realname, nickname, email, mobile, credit1, credit2, credit6, createtime  FROM ".tablename('mc_members')." WHERE uniacid = '{$_W['uniacid']}' ".$condition." ORDER BY createtime";
                $list = pdo_fetchall($sql);
                $header = array(
                    'uid' => 'UID', 'realname' => '姓名', 'groupid' => '会员组', 'mobile' => '手机', 'email' => '邮箱',
                    'credit1' => '积分', 'credit2' => '余额', 'createtime' => '注册时间',
                );
                $keys = array_keys($header);
                $html = "\xEF\xBB\xBF";
                foreach($header as $li) {
                    $html .= $li . "\t ,";
                }
                $html .= "\n";
                if(!empty($list)) {
                    $size = ceil(count($list) / 500);
                    for($i = 0; $i < $size; $i++) {
                        $buffer = array_slice($list, $i * 500, 500);
                        foreach($buffer as $row) {
                            if(strexists($row['email'], 'we7.cc')) {
                                $row['email'] = '';
                            }
                            $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
                            $row['groupid'] = $groups[$row['groupid']]['title'];
                            foreach($keys as $key) {
                                $data[] = $row[$key];
                            }
                            $user[] = implode("\t ,", $data) . "\t ,";
                            unset($data);
                        }
                    }
                    $html .= implode("\n", $user);
                }

                header("Content-type:text/csv");
                header("Content-Disposition:attachment; filename=会员数据.csv");
                echo $html;
                exit();
            }

            $sql = "SELECT uid, uniacid, groupid, realname, nickname, email, mobile, credit1, credit2, credit6, createtime, tencentuserid, vip  FROM ".tablename('mc_members')." WHERE uniacid = '{$_W['uniacid']}' ".$condition." ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            WeUtility::logging('web_source_mc_member', $sql);
            $list = pdo_fetchall($sql);
            if(!empty($list)) {
                $em_count = 0 ;
                foreach($list as $li) {
                    if(empty($li['email']) || (!empty($li['email']) && substr($li['email'], -6) == 'we7.cc' && strlen($li['email']) == 39)) {
                        $list[$em_count]['email_effective'] = 0;
                    } else {
                        $list[$em_count]['email_effective'] = 1;
                    }
                    $em_count++;
                }
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('mc_members')." WHERE uniacid = '{$_W['uniacid']}' ".$condition);
            $pager = pagination($total, $pindex, $psize);
            $stat['total'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
            $stat['today'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime('Y-m-d'), ':endtime' =>  strtotime('Y-m-d') + 86399));
        }
        if($operation == 'post') {
            $_W['page']['title'] = '编辑会员资料 - 会员 - 会员中心';
            $uid = intval($_GPC['uid']);
            $_GPC['pcate'] =  intval($_GPC['category']['parentid']) ;
            $_GPC['ccate'] =  intval($_GPC['category']['childid']);
            if ($_W['ispost'] && $_W['isajax']) {
                if ($_GPC['op1'] == 'addaddress' || $_GPC['op1'] == 'editaddress') {
                    $post = array(
                        'uniacid' => $_W['uniacid'],
                        'province' => trim($_GPC['province']),
                        'city' => trim($_GPC['city']),
                        'district' => trim($_GPC['district']),
                        'address' => trim($_GPC['detail']),
                        'uid' => intval($_GPC['uid']),
                        'username' => trim($_GPC['name']),
                        'mobile' => trim($_GPC['phone']),
                        'zipcode' => trim($_GPC['code'])
                    );
                    if ($_GPC['op1'] == 'addaddress') {
                        $sql = "SELECT COUNT(*) FROM ". tablename('mc_member_address'). " WHERE uniacid = :uniacid AND uid = :uid";
                        $exist_address = pdo_fetchcolumn($sql, array(':uniacid' => $post['uniacid'], ':uid' => $uid));
                        if (!$exist_address) {
                            $post['isdefault'] = 1;
                        }
                        pdo_insert('mc_member_address', $post);
                        $post['id'] = pdo_insertid();
                        message(error(1, $post), '', 'ajax');
                    } else {
                        pdo_update('mc_member_address', $post, array('id' => intval($_GPC['id']), 'uniacid' => $_W['uniacid']));
                        $post['id'] = intval($_GPC['id']);
                        message(error(1, $post), '', 'ajax');
                    }
                }
                if ($_GPC['op1'] == 'del') {
                    $id = intval($_GPC['id']);
                    pdo_delete('mc_member_address', array('id' => $id, 'uniacid' => $_W['uniacid']));
                    message(error(1), '', 'ajax');
                }
                if ($_GPC['op1'] == 'isdefault') {
                    $id = intval($_GPC['id']);
                    $uid = intval($_GPC['uid']);
                    pdo_update('mc_member_address', array('isdefault' => 0), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
                    pdo_update('mc_member_address', array('isdefault' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
                    message(error(1), '', 'ajax');
                }
                $uid = $_GPC['uid'];
                $password = $_GPC['password'];
                $sql = 'SELECT `uid`, `salt` FROM ' . tablename('mc_members') . " WHERE `uniacid`=:uniacid AND `uid` = :uid";
                $user = pdo_fetch($sql, array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
                if(empty($user) || $user['uid'] != $uid) {
                    exit('error');
                }
                $password = md5($password . $user['salt'] . $_W['config']['setting']['authkey']);
                if (pdo_update('mc_members', array('password' => $password), array('uid' => $uid))) {
                    exit('success');
                }
                exit('othererror');
            }

            if (checksubmit('submit')) {
                $uid = intval($_GPC['uid']);
                if (!empty($_GPC)) {
                    if (!empty($_GPC['birth'])) {
                        $_GPC['birthyear'] = $_GPC['birth']['year'];
                        $_GPC['birthmonth'] = $_GPC['birth']['month'];
                        $_GPC['birthday'] = $_GPC['birth']['day'];
                    }
                    if (!empty($_GPC['reside'])) {
                        $_GPC['resideprovince'] = $_GPC['reside']['province'];
                        $_GPC['residecity'] = $_GPC['reside']['city'];
                        $_GPC['residedist'] = $_GPC['reside']['district'];
                    }
                    unset($_GPC['uid']);
                    if(!empty($_GPC['fanid'])) {
                        if(empty($_GPC['email']) && empty($_GPC['mobile'])) {
                            $_GPC['email'] = md5($_GPC['openid']) . '@we7.cc';
                        }
                        $fanid = intval($_GPC['fanid']);
                        $struct = array_keys(mc_fields());
                        $struct[] = 'birthyear';
                        $struct[] = 'birthmonth';
                        $struct[] = 'birthday';
                        $struct[] = 'resideprovince';
                        $struct[] = 'residecity';
                        $struct[] = 'residedist';
                        $struct[] = 'groupid';
                        unset($_GPC['reside'], $_GPC['birth']);

                        foreach ($_GPC as $field => $value) {
                            if(!in_array($field, $struct)) {
                                unset($_GPC[$field]);
                            }
                        }

                        if(!empty($_GPC['avatar'])) {
                            if(strexists($_GPC['avatar'], 'attachment/images/global/avatars/avatar_')) {
                                $_GPC['avatar'] = str_replace($_W['attachurl'], '', $_GPC['avatar']);
                            }
                        }
                        $condition = '';
                        if(!empty($_GPC['email'])) {
                            $emailexists = pdo_fetchcolumn("SELECT email FROM ".tablename('mc_members')." WHERE uniacid = :uniacid AND email = :email " . $condition, array(':uniacid' => $_W['uniacid'], ':email' => trim($_GPC['email'])));
                            if($emailexists) {
                                unset($_GPC['email']);
                            }
                        }
                        if(!empty($_GPC['mobile'])) {
                            $mobilexists = pdo_fetchcolumn("SELECT mobile FROM ".tablename('mc_members')." WHERE uniacid = :uniacid AND mobile = :mobile " . $condition, array(':uniacid' => $_W['uniacid'], ':mobile' => trim($_GPC['mobile'])));
                            if($mobilexists) {
                                unset($_GPC['mobile']);
                            }
                        }
                        $_GPC['uniacid'] = $_W['uniacid'];
                        $_GPC['createtime'] = TIMESTAMP;
                        pdo_insert('mc_members', $_GPC);
                        $uid = pdo_insertid();
                        pdo_update('mc_mapping_fans', array('uid' => $uid), array('fanid' => $fanid, 'uniacid' => $_W['uniacid']));
                        message('更新资料成功！', $this->createWebUrl('member', array('uid' => $uid,'op'=>'post')), 'success');
                    } else {
                        $email_effective = intval($_GPC['email_effective']);
                        if(($email_effective == 1 && empty($_GPC['email']))) {
                            unset($_GPC['email']);
                        }
                        unset($_GPC['addresss']);
                        $uid = mc_update($uid, $_GPC);
                    }
                }
                //message('更新资料成功！', referer(), 'success');
                message('更新资料成功！', $this->createWebUrl('member', array('uid' => $uid,'op'=>'display')), 'success');
            }
            $groups = mc_groups($_W['uniacid']);
            $profile = pdo_get('mc_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
            if(!empty($profile)) {
                if(empty($profile['email']) || (!empty($profile['email']) && substr($profile['email'], -6) == 'we7.cc' && strlen($profile['email']) == 39)) {
                    $profile['email_effective'] = 1;
                    $profile['email'] = '';
                } else {
                    $profile['email_effective'] = 2;
                }
            }

            if(empty($uid)) {
                $fanid = intval($_GPC['fanid']);
                $tag = pdo_fetchcolumn('SELECT tag FROM ' . tablename('mc_mapping_fans') . ' WHERE uniacid = :uniacid AND fanid = :fanid', array(':uniacid' => $_W['uniacid'], ':fanid' => $fanid));
                if(is_base64($tag)){
                    $tag = base64_decode($tag);
                }
                if(is_serialized($tag)){
                    $fan = iunserializer($tag);
                }
                if(!empty($tag)) {
                    if(!empty($fan['nickname'])) {
                        $profile['nickname'] = $fan['nickname'];
                    }
                    if(!empty($fan['sex'])) {
                        $profile['gender'] = $fan['sex'];
                    }
                    if(!empty($fan['city'])) {
                        $profile['residecity'] = $fan['city'] . '市';
                    }
                    if(!empty($fan['province'])) {
                        $profile['resideprovince'] = $fan['province'] . '省';
                    }
                    if(!empty($fan['country'])) {
                        $profile['nationality'] = $fan['country'];
                    }
                    if(!empty($fan['headimgurl'])) {
                        $profile['avatar'] = rtrim($fan['headimgurl'], '0') . 132;
                    }
                }
            }
            $addresss = pdo_getall('mc_member_address', array('uid' => $uid, 'uniacid' => $_W['uniacid']));
        }

        if($operation == 'del') {
            $_W['page']['title'] = '删除会员资料 - 会员 - 会员中心';

            if(checksubmit('submit')) {
                if(!empty($_GPC['uid'])) {
                    $instr  = implode(',',$_GPC['uid']);
                    $uid    = $_GPC['uid'];
                    pdo_query("DELETE FROM ".tablename('mc_members')." WHERE `uniacid` = {$_W['uniacid']} AND `uid` IN ({$instr})");
                    //message('删除成功！', referer(), 'success');
                    message('删除成功！', $this->createWebUrl('member', array('uid' => $uid,'op'=>'display')), 'success');
                }
                //message('请选择要删除的项目！', referer(), 'error');
                message('请选择要删除的项目！', $this->createWebUrl('member', array('uid' => $uid,'op'=>'display')) , 'error');
            }
        }

        if($operation == 'add') {            
            if($_W['isajax']) {
                $type = trim($_GPC['type']);
                $data = trim($_GPC['data']);
                if(empty($data) || empty($type)) {
                    exit(json_encode(array('valid' => false)));
                }
                $user = pdo_get('mc_members', array('uniacid' => $_W['uniacid'], $type => $data));
                if(empty($user)) {
                    exit(json_encode(array('valid' => true)));
                } else {
                    exit(json_encode(array('valid' => false)));
                }
            }
            if(checksubmit('form')) {
                $realname = trim($_GPC['realname']) ? trim($_GPC['realname']) : message('姓名不能为空');
                $tencentuserid = trim($_GPC['tencentuserid']) ? trim($_GPC['tencentuserid']): message('tencent user can not empty');
                $pcate    = intval($_GPC['category']['parentid']) ;
                $ccate    = intval($_GPC['category']['childid']);
                $mobile = trim($_GPC['mobile']) ? trim($_GPC['mobile']) : message('手机不能为空');
                $user = pdo_get('mc_members', array('uniacid' => $_W['uniacid'], 'mobile' => $mobile));
                $notice     = $_GPC['notice'];
                $dynamic    = $_GPC['dynamic'];
                if(!empty($user)) {
                    message('手机号被占用');
                }
                $email = trim($_GPC['email']);
                if(!empty($email)) {
                    $user = pdo_get('mc_members', array('uniacid' => $_W['uniacid'], 'email' => $email));
                    if(!empty($user)) {
                        message('邮箱被占用');
                    }
                }
                $salt = random(8);
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'tencentuserid' => $tencentuserid,
                    'realname'  => $realname,
                    'nickname'  => $realname,
                    'mobile'    => $mobile,
                    'email'     => $email,
                    'notice'    => $notice,
                    'dynamic'   => $dynamic,
                    'salt'      => $salt,
                    'password'  => md5(trim($_GPC['password']) . $salt . $_W['config']['setting']['authkey']),
                    'credit1'   => intval($_GPC['credit1']),
                    'credit2'   => intval($_GPC['credit2']),
                    'groupid'   => intval($_GPC['groupid']),
                    'pcate'     => $pcate,
                    'ccate'     => $ccate,
                    'createtime' => TIMESTAMP,
                );
                pdo_insert('mc_members', $data);
                $uid = pdo_insertid();
                //message('添加会员成功,将进入编辑页面', url('mc/member/post', array('uid' => $uid)), 'success');
                message('添加会员成功,将进入编辑页面', $this->createWebUrl('member', array('op' => 'post','uid' => $uid)), 'success');
            }
        }

        if($operation == 'group') {
            if($_W['isajax']) {
                $id = intval($_GPC['id']);
                $group = $_W['account']['groups'][$id];
                if(empty($group)) {
                    exit('会员组信息不存在');
                }
                $uid = intval($_GPC['uid']);
                $member = mc_fetch($uid);
                if(empty($member)) {
                    exit('会员信息不存在');
                }
                $credit = intval($group['credit']);
                $credit6 = $credit - $member['credit1'];
                $status = pdo_update('mc_members', array('credit6' => $credit6, 'groupid' => $id), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
                if($status !== false) {
                    $openid = pdo_fetchcolumn('SELECT openid FROM ' . tablename('mc_mapping_fans') . ' WHERE acid = :acid AND uid = :uid', array(':acid' => $_W['acid'], ':uid' => $uid));
                    if(!empty($openid)) {
                        mc_notice_group($openid, $_W['account']['groups'][$member['groupid']]['title'], $_W['account']['groups'][$id]['title']);
                    }
                    exit('success');
                } else {
                    exit('更新会员信息出错');
                }
            }
            exit('error');
        }

        if($operation == 'credit_record') {
            $_W['page']['title'] = '积分日志-会员管理';
            $uid = intval($_GPC['uid']);
            $credits = array(
                'credit1' => '积分',
                'credit2' => '余额'
            );
            $type = trim($_GPC['type']) ? trim($_GPC['type']) : 'credit1';
            $pindex = max(1, intval($_GPC['page']));
            $psize = 50;
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('mc_credits_record') . ' WHERE uid = :uid AND uniacid = :uniacid AND credittype = :credittype ', array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':credittype' => $type));
            $data = pdo_fetchall("SELECT r.*, u.username FROM " . tablename('mc_credits_record') . ' AS r LEFT JOIN ' .tablename('users') . ' AS u ON r.operator = u.uid ' . ' WHERE r.uid = :uid AND r.uniacid = :uniacid AND r.credittype = :credittype ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize .',' . $psize, array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':credittype' => $type));
            $pager = pagination($total, $pindex, $psize);
            $modules = pdo_getall('modules', array('issystem' => 0), array('title', 'name'), 'name');
            $modules['card'] = array('title' => '会员卡', 'name' => 'card');
        }

        if($operation == 'credit_stat') {
            $_W['page']['title'] = '积分日志-会员管理';
            $uid = intval($_GPC['uid']);
            $credits = array(
                'credit1' => '积分',
                'credit2' => '余额'
            );
            $type = intval($_GPC['type']);
            $starttime = strtotime('-7 day');
            $endtime = strtotime('7 day');
            if($type == 1) {
                $starttime = strtotime(date('Y-m-d'));
                $endtime = TIMESTAMP;
            } elseif($type == -1) {
                $starttime = strtotime('-1 day');
                $endtime = strtotime(date('Y-m-d'));
            } else{
                $starttime = strtotime($_GPC['datelimit']['start']);
                $endtime = strtotime($_GPC['datelimit']['end']) + 86399;
            }
            if(!empty($credits)) {
                $data = array();
                foreach($credits as $key => $li) {
                    $data[$key]['add'] = round(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :id AND uid = :uid AND createtime > :start AND createtime < :end AND credittype = :type AND num > 0', array(':id' => $_W['uniacid'], ':uid' => $uid, ':start' => $starttime, ':end' => $endtime, ':type' => $key)),2);
                    $data[$key]['del'] = abs(round(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :id AND uid = :uid AND createtime > :start AND createtime < :end AND credittype = :type AND num < 0', array(':id' => $_W['uniacid'], ':uid' => $uid, ':start' => $starttime, ':end' => $endtime, ':type' => $key)),2));
                    $data[$key]['end'] = $data[$key]['add'] - $data[$key]['del'];
                }
            }
        }
        include $this->template('member');
    }

    /************************************api test********************************************/
    public function doWebCategoryApi()
    {
        $category = pdo_fetchall("SELECT * FROM " . tablename('golf_category') . "  ORDER BY parentid ASC, displayorder DESC");
        return json_encode($category);
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


	