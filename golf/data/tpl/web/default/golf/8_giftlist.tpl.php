<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common', TEMPLATE_INCLUDEPATH)) : (include template('common', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
    <li <?php  if($operation == 'post') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('giftlist', array('op' => 'post'));?>">添加文章</a></li>
    <li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('giftlist', array('op' => 'display'));?>">文章列表</a></li>
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
            <input type="hidden" name="do" value="giftlist" />
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
                <th style="width:180px;">Max Size</th>
                <th style="width:180px;">Stock</th>
                <th style="width:180px;">属性</th>
                <th style="width:200px; text-align:right;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($list)) { foreach($list as $item) { ?>
            <tr>
                <td><?php  if(!empty($item['pcate'])) { ?><span><?php  echo $item['displayorder'];?></span><?php  } else { ?><?php  echo $item['displayorder'];?><?php  } ?></td>
                <td>
                    <?php  if(!empty($item['pcate'])) { ?><span class="text-error">[<?php  echo $category[$item['pcate']]['name'];?>]</span><?php  } ?><?php  if(!empty($item['ccate'])) { ?><span class="text-info">[<?php  echo $category[$item['ccate']]['name'];?>]</span><?php  } ?>
                    <a href="<?php  echo $this->createWebUrl('giftlist', array('id' => $item['id']))?>" style="color:#333;"><?php  echo $item['title'];?></a>
                </td>
                <td>
                    <?php  if($item['max_size'] == '1') { ?><span class="label label-info">1</span><?php  } ?>
                    <?php  if($item['max_size'] == '10') { ?><span class="label label-info">10</span><?php  } ?>
                    <?php  if($item['max_size'] == '100') { ?><span class="label label-info">100</span><?php  } ?>
                    <?php  if($item['max_size'] == '1000') { ?><span class="label label-info">1000</span><?php  } ?>
                    <?php  if($item['max_size'] == '10001') { ?><span class="label label-info">bigger than 1000</span><?php  } ?>
                </td>
                <td>
                    <?php  echo $item['stock'];?>
                </td>
                <td>
                    <?php  if($item['ishot']) { ?><span class="label label-success">头条</span><?php  } ?>
                    <?php  if($item['iscommend']) { ?><span class="label label-warning">推荐</span><?php  } ?>
                    <?php  if($item['isnew']) { ?> <span class="label label-info">New</span><?php  } ?>
                </td>
                <td style="text-align:right; position:relative;">
                    <a href="<?php  echo $this->createWebUrl('giftlist' , array('id' => $item['id'],'op' => 'post'))?>" title="编辑">编辑</a>&nbsp;-&nbsp;
                    <a onclick="return confirm('此操作不可恢复，确认吗？'); return false;" href="<?php  echo $this->createWebUrl('giftlist' , array('id' => $item['id'],'op'=>'delete'))?>" title="删除">删除</a>
                </td>
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
<?php  } else if($operation == 'post') { ?>
<div class="clearfix">
    <form class="form-horizontal form" action="" method="post" enctype="multipart/form-data">
        <div class="panel panel-default">
            <div class="panel-heading">文章管理</div>
            <div class="panel-body">
                <input type="hidden" name="id" value="<?php  echo $item['id'];?>">
                <?php  if(!empty($item) && empty($item['linkurl']) && $id > 0) { ?>
                <?php  } ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">排序</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control" placeholder="" name="displayorder" value="<?php  echo $item['displayorder'];?>">
                        <span class="help-block">文章的显示顺序，越大则越靠前</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">标题</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control" placeholder="" name="title" value="<?php  echo $item['title'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">自定义属性</label>
                    <div class="col-sm-8 col-xs-12">
                        <label class="checkbox-inline"><input type="checkbox" name="option[hot]" value="1" <?php  if($item['ishot']) { ?> checked<?php  } ?>> 头条[h]</label>
                        <label class="checkbox-inline"><input type="checkbox" name="option[commend]" value="1" <?php  if($item['iscommend']) { ?> checked<?php  } ?>> 推荐[c]</label>
                        <label class="checkbox-inline"><input type="checkbox" name="option[new]" value="1" <?php  if($item['isnew']) { ?> checked<?php  } ?>> New[n]</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">缩略图</label>
                    <div class="col-sm-8 col-xs-12">
                        <?php  echo tpl_form_field_image('thumb', $item['thumb'])?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-9 col-xs-12">
                        <label>
                            封面（大图片建议尺寸：360像素 * 200像素）
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">文章类别</label>
                    <div class="col-sm-8 col-xs-12">
                        <?php  echo tpl_form_field_category_2level('category', $parent, $children, $pcate, $ccate)?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">简介</label>
                    <div class="col-sm-8 col-xs-12">
                        <textarea class="form-control" name="description" rows="5"><?php  echo $item['description'];?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Price</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="number" class="form-control" placeholder="" name="price" value="<?php  echo $item['price'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Gift Max Size</label>
                    <div class="col-sm-8 col-xs-12">
                        <select name="max_size" class="form-control">
                            <option value="1" <?php  if($item['max_size'] == '1') { ?> selected <?php  } ?> > 1</option>
                            <option value="10" <?php  if($item['max_size'] == '10') { ?> selected <?php  } ?> > 10</option>
                            <option value="100" <?php  if($item['max_size'] == '100') { ?> selected <?php  } ?>> 100</option>
                            <option value="1000" <?php  if($item['max_size'] == '1000') { ?> selected <?php  } ?> > 1000</option>
                            <option value="1001" <?php  if($item['max_size'] == '1001') { ?> selected <?php  } ?> > Bigger than 1000</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Stock</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="number" class="form-control" placeholder="" name="stock" value="<?php  echo $item['stock'];?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <input name="submit" type="submit" value="提交" class="btn btn-primary col-lg-1">
                <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    var category = <?php  echo json_encode($children)?>;
    $('#credit1').click(function(){
        $('#credit-status1').show();
    });
    $('#credit0').click(function(){
        $('#credit-status1').hide();
    });
</script>

<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
