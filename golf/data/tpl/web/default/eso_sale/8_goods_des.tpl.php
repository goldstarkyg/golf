<?php defined('IN_IA') or exit('Access Denied');?><div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品简介</label>
    <div class="col-sm-9 col-xs-12">
        <textarea style="height:120px;" class="form-control" id="description" name="description"><?php  echo $item['description'];?></textarea>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品详情</label>
    <div class="col-sm-9 col-xs-12">
        <!--<textarea style="height:380px;" class="form-control richtext-clone" name="content"><?php  echo $item['content'];?></textarea>-->
        <textarea style="height:380px;" class="form-control " name="content"><?php  echo $item['content'];?></textarea>
    </div>
</div>