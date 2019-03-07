<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common', TEMPLATE_INCLUDEPATH)) : (include template('common', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
    <li <?php  if($operation == 'post') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('videotag', array('op' => 'post'))?>">Add Video Tag</a></li>
    <li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('videotag', array('op' => 'display'))?>">Video Tag List</a></li>
</ul>
<?php  if($operation == 'post') { ?>
<div class="main">
    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data">
        <div class="panel panel-default">
            <div class="panel-heading">
                分类详细设置
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">排序</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="text" name="displayorder" class="form-control" value="<?php  echo $videotag['displayorder'];?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">Title</label>
                    <div class="col-sm-9 col-xs-12">
                        <input type="text" name="title" class="form-control" value="<?php  echo $videotag['title'];?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">Hot</label>
                    <div class="col-sm-9 col-xs-12">
                        <label class="radio-inline">
                            <input type='radio' name='ishot' class="check" value=1' <?php  if($videotag['ishot']==1) { ?>checked<?php  } ?> /> 是
                        </label>
                        <label class="radio-inline">
                            <input type='radio' name='ishot' class="check" value=0' <?php  if($videotag['ishot']==0) { ?>checked<?php  } ?> /> 否
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">首页推荐</label>
                    <div class="col-sm-9 col-xs-12">
                        <label class="radio-inline">
                            <input type='radio' name='isrecommend' class="check" value=1' <?php  if($videotag['isrecommend']==1) { ?>checked<?php  } ?> /> 是
                        </label>
                        <label class="radio-inline">
                            <input type='radio' name='isrecommend' class="check" value=0' <?php  if($videotag['isrecommend']==0) { ?>checked<?php  } ?> /> 否
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <input name="submit" type="submit" value="提交" class="btn btn-primary span3">
        <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
    </form>
</div>


<script type="text/javascript" src="./resource/script/colorpicker/spectrum.js"></script>
<link type="text/css" rel="stylesheet" href="./resource/script/colorpicker/spectrum.css" />
<script type="text/javascript">
    <!--
    $(function(){
        colorpicker();
    });
    //-->
</script>
<?php  } else if($operation == 'display') { ?>
<div class="main">
    <div class="category">
        <form action="" method="post" onsubmit="return formcheck(this)">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th style="width:10px;"></th>
                    <th style="width:120px;">显示顺序</th>
                    <th>分类名称</th>
                    <th>Hot</th>
                    <th>Recommend</th>
                    <th style="width:150px;">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php  if(is_array($videotag)) { foreach($videotag as $row) { ?>
                <tr>
                    <td><?php  if(count($children[$row['id']]) > 0) { ?><a href="javascript:;"><i class="icon-chevron-down"></i></a><?php  } ?></td>
                    <td><input type="text" class="form-control" name="displayorder[<?php  echo $row['id'];?>]" value="<?php  echo $row['displayorder'];?>"></td>
                    <td> <?php  echo $row['title'];?></td>
                    <td> <?php  if($row['ishot']== '1' ) { ?> 是 <?php  } else { ?> 否 <?php  } ?></td>
                    <td> <?php  if($row['isrecommend'] == '1') { ?> 是 <?php  } else { ?> 否 <?php  } ?> </td>
                    <td><a href="<?php  echo $this->createWebUrl('videotag', array('op' => 'post', 'id' => $row['id']))?>">编辑</a>&nbsp;&nbsp;
                        <a href="<?php  echo $this->createWebUrl('videotag', array('op' => 'delete', 'id' => $row['id']))?>"
                                onclick="return confirm('确认删除此分类吗？');return false;">删除</a></td>
                </tr>
                <?php  } } ?>
                <tr>
                    <td colspan="6">
                        <a href="<?php  echo $this->createWebUrl('videotag', array('op' => 'post'))?>"><i class="icon-plus-sign-alt"></i> 添加新分类</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <input name="submit" type="submit" class="btn btn-primary" value="提交">
                        <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
