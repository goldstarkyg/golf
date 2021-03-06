<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<ul class="nav nav-tabs">
    <li <?php  if($operation == 'post') { ?>class="active"<?php  } ?>>
    <a href="<?php  echo create_url('site/entry/award', array('m' => 'eso_sale', 'op' => 'post'));?>">添加</a></li>
    <li <?php  if($operation == 'display') { ?>class="active"<?php  } ?>>
    <a href="<?php  echo create_url('site/entry/award', array('m' => 'eso_sale', 'op' => 'display'));?>">管理</a>
    </li>
</ul>
<?php  if($operation == 'post') { ?>
<div class="main">
    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php  echo $item['id'];?>" />

        <div class="panel panel-default">
            <div class="panel-heading">
                <?php  if(empty($item['id'])) { ?>添加奖品<?php  } else { ?>编辑奖品<?php  } ?>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">奖品名称</label>
                    <div class="col-sm-6 col-xs-6">
                        <input type="text" name="title" class="form-control" value="<?php  echo $item['title'];?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">宣传图</label>
                    <div class="col-sm-6 col-xs-6">
                        <?php echo tpl_form_field_image('logo', $item['logo'] =='' ? $setting['thumb'] : $item['logo']);?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">剩余可兑换奖品数</label>
                    <div class="col-sm-6 col-xs-6">
                        <input type="text" name="amount" class="form-control" value="<?php  echo $item['amount'];?>" />
                        <div class="help-block">此设置项设置该奖品剩余奖品数。为0时不对外显示，不接受兑换。</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">兑奖截止日期</label>
                    <div class="col-sm-6 col-xs-6">
                        <?php  echo tpl_form_field_date('deadline',$item['deadline'], true)?>
                        <div class="help-block">超过该日期后不可兑奖!</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">奖品实际价格</label>
                    <div class="col-sm-6 col-xs-6">
                        <input type="text" name="price" class="form-control" value="<?php  echo $item['price'];?>" />
                        <span class="help-block">此设置项设置奖品价格。</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">兑换消耗积分数</label>
                    <div class="col-sm-6 col-xs-6">
                        <input type="text" name="credit_cost" class="form-control" value="<?php  echo $item['credit_cost'];?>" />
                        <span class="help-block">此设置项设置该奖品剩余奖品数。为0时不对外显示，不接受兑换。</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">内容</label>
                    <div class="col-sm-6 col-xs-6">
                        <textarea class="form-control richtext-clone" name="content" cols="70"><?php  echo $item['content'];?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="col-sm-6 col-xs-6">
                        <input name="submit" type="submit" value="提交" class="btn btn-primary span3">
                        <input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
                    </div>
                </div>

            </div>
        </div>

    </form>
</div>
<script type="text/javascript">
    require(['jquery', 'util'], function($, u){
        $(function(){
            u.editor($('.richtext-clone')[0]);
        });
    });
</script>
<?php  } else if($operation == 'display') { ?>
<div class="main">
    <div style="padding:15px;">
        <table class="table table-hover">
            <thead class="navbar-inner">
            <tr>
                <th>奖品名称</th>
                <th>剩余数量</th>
                <th>价格</th>
                <th>消耗积分</th>
                <!--<th>描述</th>-->
                <th style="width:120px;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($list)) { foreach($list as $item) { ?>
            <tr>
                <td><?php  echo $item['title'];?></td>
                <td><?php  echo $item['amount'];?></td>
                <td><?php  echo $item['price'];?></td>
                <td><?php  echo $item['credit_cost'];?></td>
              <!--  <td><?php  echo cutstr(htmlspecialchars_decode($item['content']),100)?></td>-->
                <td>
                    <a href="<?php  echo create_url('site/entry/award', array('m' => 'eso_sale', 'award_id' => $item['award_id'], 'op' => 'post'))?>" title="编辑" class="btn btn-default btn-sm"><i class="fa fa-edit"></i>
                    </a>
                    <a href="<?php  echo create_url('site/entry/award', array('m' => 'eso_sale', 'award_id' => $item['award_id'], 'op' => 'delete'))?>" onclick="return confirm('此操作不可恢复，确认删除？');return false;" title="删除" class="btn btn-default btn-sm"><i class="fa fa-remove"></i>
                    </a>
                </td>
            </tr>
            <?php  } } ?>
            </tbody>
            <!--tr>
                <td colspan="8">
                    <input name="token" type="hidden" value="<?php  echo $_W['token'];?>" />
                    <input type="submit" class="btn btn-primary" name="submit" value="提交" />
                </td>
            </tr-->
        </table>
    </div>
</div>
<?php  } ?>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
