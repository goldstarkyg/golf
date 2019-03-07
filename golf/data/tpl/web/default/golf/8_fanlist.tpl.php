<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common', TEMPLATE_INCLUDEPATH)) : (include template('common', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
    <li <?php  if($operation == 'post') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('fanlist', array('op' => 'post'));?>">添加文章</a></li>
    <li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('fanlist', array('op' => 'display'));?>">文章列表</a></li>
</ul>
<style>
    .table td span{display:inline-block;margin-top:4px;}
    .table td input{margin-bottom:0;}
</style>
<?php  if($operation == 'display') { ?>
<div class="panel panel-default">
    <div class="table-responsive panel-body">
        <table class="table">
            <thead>
            <tr>
                <th style="width:50px">排序</th>
                <th>标题</th>
                <th>Size</th>
                <th style="width:180px;">属性</th>
                <th style="width:200px; text-align:right;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($list)) { foreach($list as $item) { ?>
            <tr>
                <td><span><?php  echo $item['displayorder'];?></span></td>
                <td>
                    <a href="<?php  echo $this->createWebUrl('fanlist', array('id' => $item['id']))?>" style="color:#333;"><?php  echo $item['title'];?></a>
                </td>
                <td>
                    <?php  if($item['layer'] == '1') { ?><span class="label label-info">1个月</span><?php  } ?>
                    <?php  if($item['layer'] == '3') { ?><span class="label label-info">3个月</span><?php  } ?>
                    <?php  if($item['layer'] == '6') { ?><span class="label label-info">6个月</span><?php  } ?>
                    <?php  if($item['layer'] == '12') { ?><span class="label label-info">12个月</span><?php  } ?>
                </td>
                <td>
                    <?php  if($item['iscommend']) { ?><span class="label label-warning">推荐</span><?php  } ?>
                </td>
                <td style="text-align:right; position:relative;">
                    <a href="<?php  echo $this->createWebUrl('fanlist' , array('id' => $item['id'],'op' => 'post'))?>" title="编辑">编辑</a>&nbsp;-&nbsp;
                    <a onclick="return confirm('此操作不可恢复，确认吗？'); return false;" href="<?php  echo $this->createWebUrl('fanlist' , array('id' => $item['id'],'op'=>'delete'))?>" title="删除">删除</a>
                </td>
            </tr>
            <?php  } } ?>
            </tbody>
        </table>
    </div>
</div>
<?php  } else if($operation == 'post') { ?>
<div class="clearfix">
    <form class="form-horizontal form" action="" method="post" enctype="multipart/form-data">
        <div class="panel panel-default">
            <div class="panel-heading">文章管理</div>
            <div class="panel-body">
                <input type="hidden" name="id" value="<?php  echo $item['id'];?>">
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
                        <label class="checkbox-inline"><input type="checkbox" name="option[commend]" value="1" <?php  if($item['iscommend']) { ?> checked<?php  } ?>> 推荐[c]</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Price</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="number" class="form-control" placeholder="" name="price" value="<?php  echo $item['price'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Fan Size</label>
                    <div class="col-sm-8 col-xs-12">
                        <select name="layer" class="form-control">
                            <option value="1" <?php  if($item['layer'] == '1') { ?> selected <?php  } ?> > 1个月</option>
                            <option value="3" <?php  if($item['layer'] == '3') { ?> selected <?php  } ?> > 3个月</option>
                            <option value="6" <?php  if($item['layer'] == '6') { ?> selected <?php  } ?>> 6个月</option>
                            <option value="12" <?php  if($item['max_size'] == '12') { ?> selected <?php  } ?> > 12个月</option>
                        </select>
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
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
