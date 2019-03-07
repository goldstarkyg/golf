<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common', TEMPLATE_INCLUDEPATH)) : (include template('common', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
    <!--<li <?php  if($operation == 'post') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('vodvideo', array('op' => 'post'));?>">Add Video</a></li>-->
    <li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>><a href="<?php  echo $this->createWebUrl('vodvideo', array('op' => 'display'));?>">Video List</a></li>
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
            <input type="hidden" name="do" value="vodvideo" />
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
                <th style="width:150px;">User Id</th>
                <th>Title</th>
                <th style="width:180px;">View Count</th>
                <th style="width:180px;">Attribute</th>
                <th style="width:200px; text-align:right;">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($list)) { foreach($list as $item) { ?>
            <tr>
                <td><?php  if(!empty($item['pcate'])) { ?><span><?php  echo $item['userid'];?></span><?php  } else { ?><?php  echo $item['userid'];?><?php  } ?></td>
                <td>
                    <?php  if(!empty($item['pcate'])) { ?><span class="text-error">[<?php  echo $category[$item['pcate']]['name'];?>]</span><?php  } ?><?php  if(!empty($item['ccate'])) { ?><span class="text-info">[<?php  echo $category[$item['ccate']]['name'];?>]</span><?php  } ?>
                    <a href="<?php  echo $this->createWebUrl('vodvideo', array('file_id' => $item['file_id'],'op' => 'post'))?>" style="color:#333;"><?php  echo $item['title'];?></a>
                </td>
                <td><?php  echo $item['viewer_count'];?></td>
                <td>
                    <label data='<?php  echo $item['isnew'];?>' class='label label-default <?php  if($item['isnew']==1) { ?>label-info<?php  } ?>' onclick="setProperty(this,'<?php  echo $item['file_id'];?>','new')">新品</label>
                    <label data='<?php  echo $item['ishot'];?>' class='label label-default <?php  if($item['ishot']==1) { ?>label-info<?php  } ?>' onclick="setProperty(this,'<?php  echo $item['file_id'];?>','hot')">热卖</label>
                    <label data='<?php  echo $item['iscommend'];?>' class='label label-default <?php  if($item['iscommend']==1) { ?>label-info<?php  } ?>' onclick="setProperty(this,'<?php  echo $item['file_id'];?>','commend')">首页</label>

                </td>
                <td style="text-align:right; position:relative;">
                    <a href="<?php  echo $this->createWebUrl('vodvideo' , array('userid' => $item['userid'],'file_id'=>$item['file_id'],'op' => 'post'))?>" title="编辑">编辑</a>&nbsp;-&nbsp;
                    <a onclick="return confirm('此操作不可恢复，确认吗？'); return false;" href="<?php  echo $this->createWebUrl('vodvideo' , array('file_id' => $item['file_id'],'op'=>'delete'))?>" title="删除">删除</a>
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
    function setProperty(obj,id,type){
        $(obj).html($(obj).html() + "...");
        $.post("<?php  echo $this->createWebUrl('setvodvideoproperty')?>"
                ,{id:id,type:type, data: obj.getAttribute("data")}
                ,function(d){
                    $(obj).html($(obj).html().replace("...",""));
                    $(obj).attr("data",d.data)
                    if(d.result==1){
                        $(obj).toggleClass("label-info");
                    }
                },"json");
    }
</script>
<?php  } else if($operation == 'post') { ?>
<div class="clearfix">
    <form class="form-horizontal form" action="" method="post" enctype="multipart/form-data">
        <div class="panel panel-default">
            <div class="panel-heading">文章管理</div>
            <div class="panel-body">
                <input type="hidden" name="id" value="<?php  echo $item['id'];?>">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Viewer Count</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control" placeholder="" name="viewer_count" value="<?php  echo $item['viewer_count'];?>">
                        <span class="help-block">文章的显示顺序，越大则越靠前</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Like Count</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control"  name="like_count" value="<?php  echo $item['like_count'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Video Title</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control" placeholder="" name="title" value="<?php  echo $item['title'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">自定义属性</label>
                    <div class="col-sm-8 col-xs-12">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="option[hot]" value="1" <?php  if($item['ishot']) { ?> checked<?php  } ?>> 头条[h]
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="option[commend]" value="1" <?php  if($item['iscommend']) { ?> checked<?php  } ?>> 推荐[c]</label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="option[new]" value="1"  <?php  if($item['isnew'] == 1) { ?> checked <?php  } ?> /> New[n]
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Video Tag</label>
                    <div class="col-sm-8 col-xs-12">
                        <?php  if(is_array($selected_video)) { foreach($selected_video as $li) { ?>
                        <label class="checkbox-inline" style="padding-top: 0px;">
                            <input type="checkbox" name="ids[]" value="<?php  echo $li['file_id'];?>_<?php  echo $li['id'];?>" <?php  if($li['selected_video']== '1') { ?> checked <?php  } ?>> <?php  echo $li['title'];?>
                        </label>
                        <?php  } } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">File Id</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control" name="file_id" readonly value="<?php  echo $item['file_id'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Nick Name</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control"  name="nickname" readonly value="<?php  echo $item['nickname'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Head picture</label>
                    <div class="col-sm-8 col-xs-12">
                        <?php  echo tpl_form_field_image('headpic', $item['headpic'])?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Front Cover</label>
                    <div class="col-sm-8 col-xs-12">
                        <?php  echo tpl_form_field_image('frontcover', $item['frontcover'])?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Location</label>
                    <div class="col-sm-8 col-xs-12">
                        <input type="text" class="form-control"  name="location" readonly value="<?php  echo $item['location'];?>">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Description</label>
                <div class="col-sm-8 col-xs-12">
                    <textarea class="form-control" name="desc" rows="5"><?php  echo $item['desc'];?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">Play Url</label>
                <div class="col-sm-8 col-xs-12">
                    <input type="text" class="form-control"  name="play_url" readonly value="<?php  echo $item['play_url'];?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label">文章类别</label>
                <div class="col-sm-8 col-xs-12">
                    <?php  echo tpl_form_field_category_2level('category', $parent, $children, $pcate, $ccate)?>
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
