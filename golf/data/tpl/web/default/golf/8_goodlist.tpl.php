<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common', TEMPLATE_INCLUDEPATH)) : (include template('common', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
    <li <?php  if($operation == 'display_good') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('goodlist', array('op' => 'display_good'));?>">Add Good</a></li>
    <li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('goodlist', array('op' => 'display'));?>">Good List</a></li>
</ul>
<style>
    .table td span{display:inline-block;margin-top:4px;}
    .table td input{margin-bottom:0;}
</style>
<?php  if($operation == 'display') { ?>
<div class="panel panel-info">
    <div class="panel-heading">筛选</div>
    <div class="panel-body">
        <form action="" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
            <input type="hidden" name="c" value="site" />
            <input type="hidden" name="a" value="entry" />
            <input type="hidden" name="do" value="goodlist" />
            <input type="hidden" name="m" value="golf" />
            <input type="hidden" name="op" value="display" />
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 control-label">关键字</label>
                <div class="col-sm-8 col-md-8 col-lg-8 col-xs-12">
                    <input class="form-control" name="keyword" id="" type="text" value="<?php  echo $_GPC['keyword'];?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-md-2 control-label">文章分类</label>
                <div class="col-sm-8 col-md-8 col-lg-8 col-xs-12">
                    <?php  echo tpl_form_field_category_2level('category', $parent, $children, $_GPC['category']['parentid'], $_GPC['category']['childid']);?>
                </div>
                <div class="pull-right col-xs-12 col-sm-2 col-md-2 col-lg-2">
                    <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="table-responsive panel-body">
        <table class="table">
            <thead>
            <tr>
                <th style="width:50px">排序</th>
                <th>标题</th>
                <th style="width:180px;">属性</th>
                <!--<th style="width:200px; text-align:right;">操作</th>-->
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($list)) { foreach($list as $item) { ?>
            <tr>
                <td><?php  if(!empty($item['pcate'])) { ?><span><?php  echo $item['displayorder'];?></span><?php  } else { ?><?php  echo $item['displayorder'];?><?php  } ?></td>
                <td>
                    <?php  if(!empty($item['pcate'])) { ?><span class="text-error">[<?php  echo $category[$item['category_id']]['name'];?>]</span><?php  } ?>
                    <a target="_blank" href="<?php  echo url('site/entry/goods/', array('m'=>'eso_sale','op'=>'post','id' => $item['id']))?>" style="color:#333;"><?php  echo $item['title'];?></a>
                </td>
                <td>
                    <?php  if($item['ishot']) { ?><span class="label label-success">头条</span><?php  } ?>
                    <?php  if($item['iscommend']) { ?><span class="label label-warning">推荐</span><?php  } ?>
                    <?php  if($item['isnew']) { ?> <span class="label label-info">New</span><?php  } ?>
                </td>
                <!--<td style="text-align:right; position:relative;">-->
                    <!--<a href="<?php  echo $this->createWebUrl('goodlist' , array('id' => $item['id'],'op' => 'post'))?>" title="编辑">编辑</a>&nbsp;-&nbsp;-->
                    <!--<a onclick="return confirm('此操作不可恢复，确认吗？'); return false;" href="<?php  echo $this->createWebUrl('goodlist' , array('id' => $item['id'],'op'=>'delete'))?>" title="删除">删除</a>-->
                <!--</td>-->
            </tr>
            <?php  } } ?>
            </tbody>
        </table>
    </div>
</div>
<?php  echo $pager;?>
<script type="text/javascript">
    var category = <?php  echo json_encode($children)?>;
</script>
<?php  } else if($operation == 'display_good' ) { ?>
<div class="clearfix">
        <div class="panel panel-default">
            <div class="panel-heading">文章管理</div>
            <div class="panel-body">
                <div class="panel-body">
                    <form action="" method="post" class="form-horizontal" role="form" enctype="multipart/form-data" onsubmit="return checkCategory(this);" >
                        <input type="hidden" name="c" value="site" />
                        <input type="hidden" name="a" value="entry" />
                        <input type="hidden" name="do" value="goodlist" />
                        <input type="hidden" name="m" value="golf" />
                        <input type="hidden" name="op" value="display_good" />
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-2 col-md-2 control-label">Main Category</label>
                                <div class="col-sm-8 col-md-8 col-lg-8 col-xs-12">
                                    <?php  echo tpl_form_field_category_2level('category', $parent, $children, $_GPC['category']['parentid'], $_GPC['category']['childid']);?>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" id="message_div" style="display: none">
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-2 col-md-2 control-label"></label>
                                <div class="col-sm-8 col-md-8 col-lg-8 col-xs-12" id="err_message">

                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 col-md-2 control-label">Good Key</label>
                            <div class="col-sm-8 col-md-8 col-lg-8 col-xs-12">
                                <input class="form-control" name="keyword" id="" type="text" value="<?php  echo $_GPC['keyword'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-2 col-md-2 control-label">分类</label>
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <select class="form-control" style="margin-right:15px;" name="cate_1" onchange="selectChildCategory(this.options[this.selectedIndex].value)">
                                    <option value="0">请选择一级分类</option>
                                    <?php  if(is_array($good_category)) { foreach($good_category as $row) { ?>
                                    <?php  if($row['parentid'] == 0) { ?>
                                    <option value="<?php  echo $row['id'];?>" <?php  if($row['id'] == $_GPC['cate_1']) { ?> selected="selected"<?php  } ?>><?php  echo $row['name'];?></option>
                                    <?php  } ?>
                                    <?php  } } ?>
                                </select>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <select class="form-control input-medium" id="cate_2" name="cate_2">
                                    <option value="0">请选择二级分类</option>
                                    <?php  if(!empty($_GPC['cate_1']) && !empty($children[$_GPC['cate_1']])) { ?>
                                    <?php  if(is_array($good_children[$_GPC['cate_1']])) { foreach($good_children[$_GPC['cate_1']] as $row) { ?>
                                    <option value="<?php  echo $row['0'];?>" <?php  if($row['0'] == $_GPC['cate_2']) { ?> selected="selected"<?php  } ?>><?php  echo $row['1'];?></option>
                                    <?php  } } ?>
                                    <?php  } ?>
                                </select>
                            </div>
                            <div class=" col-xs-12 col-sm-2 col-lg-2 col-md-2">
                                <button class="btn btn-default" ><i class="fa fa-search"></i> 搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <form method="post" class="form-horizontal" id="postform"  onsubmit="return checkStatus(this);">
                <input type="hidden" name="c" value="site" />
                <input type="hidden" name="a" value="entry" />
                <input type="hidden" name="do" value="goodlist" />
                <input type="hidden" name="m" value="golf" />
                <input type="hidden" name="op" value="post" />
                <input type="hidden" name="post_category_id" id="post_category_id">
            <div style="padding:15px;">
                <table class="table table-hover">
                    <thead class="navbar-inner">
                    <tr>
                        <th style="width:60px;">Status</th>
                        <th style="width:60px;">ID</th>
                        <th>商品标题</th>
                        <th>商品属性(点击可修改)</th>
                        <th>商品编号</th>
                        <th>商品条码</th>
                        <th>状态(点击可修改)</th>
                        <!--<th style="width:120px;">操作</th>-->
                    </tr>
                    </thead>
                    <tbody>
                    <?php  if(is_array($list)) { foreach($list as $item) { ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="good_status[]" value="<?php  echo $item['id'];?>" <?php  if($item['good_status']== 1) { ?> checked <?php  } ?>  />
                        </td>
                        <td><?php  echo $item['id'];?></td>
                        <td><?php  if(!empty($category[$item['pcate']])) { ?><span class="text-error">[<?php  echo $category[$item['pcate']]['name'];?>] </span><?php  } ?><?php  if(!empty($children[$item['pcate']])) { ?><span class="text-info">[<?php  echo $children[$item['pcate']][$item['ccate']]['1'];?>] </span><?php  } ?><?php  echo $item['title'];?>
                        </td>
                        <td>
                            <label data='<?php  echo $item['isnew'];?>' class='label label-default <?php  if($item['isnew']==1) { ?>label-info<?php  } ?>' onclick="setProperty(this,<?php  echo $item['id'];?>,'new')">新品</label>
                            <label data='<?php  echo $item['ishot'];?>' class='label label-default <?php  if($item['ishot']==1) { ?>label-info<?php  } ?>' onclick="setProperty(this,<?php  echo $item['id'];?>,'hot')">热卖</label>
                            <label data='<?php  echo $item['isrecommand'];?>' class='label label-default <?php  if($item['isrecommand']==1) { ?>label-info<?php  } ?>' onclick="setProperty(this,<?php  echo $item['id'];?>,'recommand')">首页</label>
                            <label data='<?php  echo $item['isdiscount'];?>' class='label label-default <?php  if($item['isdiscount']==1) { ?>label-info<?php  } ?>' onclick="setProperty(this,<?php  echo $item['id'];?>,'discount')">折扣</label></td>
                        <td><?php  echo $item['goodssn'];?></td>
                        <td><?php  echo $item['productsn'];?></td>
                        <td><?php  if($item['status']) { ?><span data='<?php  echo $item['status'];?>' onclick="setProperty1(this,<?php  echo $item['id'];?>,'status')" class="label label-default label-success" style="cursor:pointer;">销售中</span><?php  } else { ?><span data='<?php  echo $item['status'];?>' onclick="setProperty1(this,<?php  echo $item['id'];?>,'status')" class="label label-default" style="cursor:pointer;">已下架</span><?php  } ?></td>
                        <!--<td>-->
                            <!--<a href="<?php  echo $this->createWebUrl('goods', array('id' => $item['id'], 'op' => 'post'))?>">编辑</a>&nbsp;&nbsp;-->
                            <!--<a href="<?php  echo $this->createWebUrl('goods', array('id' => $item['id'], 'op' => 'delete'))?>" onclick="return confirm('此操作不可恢复，确认删除？');return false;">删除</a>-->
                        <!--</td>-->
                    </tr>
                    <?php  } } ?>
                    </tbody>
                    <tr>
                        <td><input type="checkbox" name="" onclick="var ck = this.checked;$(':checkbox').each(function(){this.checked = ck});"></td>
                        <input name="token" type="hidden" value="<?php  echo $_W['token'];?>" />
                        <td colspan="6"><input type="submit" name="submit" class="btn btn-primary" value="删除"></td>
                    </tr>
                </table>
                <?php  echo $pager;?>
            </div>
            </form>
        </div>
</div>

<script type="text/javascript">
    var category = <?php  echo json_encode($children)?>;
    var good_category = <?php  echo json_encode($good_children)?>;
    $('#credit1').click(function(){
        $('#credit-status1').show();
    });
    $('#credit0').click(function(){
        $('#credit-status1').hide();
    });

    function selectChildCategory(cid) {
        var html = '<option value="0">请选择二级分类</option>';
        if (!good_category || !good_category[cid]) {
            $('#cate_2').html(html);
            return false;
        }
        for (i in good_category[cid]) {
            html += '<option value="'+good_category[cid][i]['id']+'">'+good_category[cid][i]['name']+'</option>';
        }
        $('#cate_2').html(html);
    }
    function checkCategory() {
        var main_category_id = $('#category_parent').val();
        if(main_category_id == '0') {
            $('#err_message').html('Please select Main category!');
            $('#message_div').show();
            return false;
        }
    }
    function checkStatus(){
        var main_category_id = $('#category_parent').val();
        $('#post_category_id').val(main_category_id);
    }
</script>

<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
