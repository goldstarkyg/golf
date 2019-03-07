<?php defined('IN_IA') or exit('Access Denied');?><div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span>商品名称</label>
    <div class="col-sm-9 col-xs-12">
        <input type="text" name="goodsname" id="goodsname" class="form-control" value="<?php  echo $item['title'];?>" />
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品属性</label>
    <div class="col-sm-9 col-xs-12" >
        <label for="isrecommand" class="checkbox-inline">
            <input type="checkbox" name="isrecommand" value="1" id="isrecommand" <?php  if($item['isrecommand'] == 1) { ?>checked="true"<?php  } ?> /> 首页推荐
        </label>
        <label for="isnew" class="checkbox-inline">
            <input type="checkbox" name="isnew" value="1" id="isnew" <?php  if($item['isnew'] == 1) { ?>checked="true"<?php  } ?> /> 新品推荐
        </label>
        <label for="ishot" class="checkbox-inline">
            <input type="checkbox" name="ishot" value="1" id="ishot" <?php  if($item['ishot'] == 1) { ?>checked="true"<?php  } ?> /> 热卖推荐
        </label>
        <label for="isdiscount" class="checkbox-inline">
            <input type="checkbox" name="isdiscount" value="1" id="isdiscount" <?php  if($item['isdiscount'] == 1) { ?>checked="true"<?php  } ?> /> 折扣商品
        </label>
        <label for="istime" class="checkbox-inline">
            <input type="checkbox" name="istime" value="1" id="istime" <?php  if($item['istime'] == 1) { ?>checked="true"<?php  } ?> /> 限时卖
        </label>
        <label for="istime" class="checkbox-inline">
            <input type="checkbox" name="isadv" value="1" id="isadv" <?php  if($item['isadv'] == 1) { ?>checked="true"<?php  } ?> /> advertisement
        </label>
    </div>
</div>
<div class="form-group">
	<label class="col-xs-12 col-sm-3 col-md-2 control-label">限时卖时间</label>
	<div class="col-sm-4 col-xs-6"><?php echo tpl_form_field_date('timestart', !empty($item['timestart']) ? date('Y-m-d H:i',$item['timestart']) : date('Y-m-d H:i'), 1)?></div>
	<div class="col-sm-4 col-xs-6"><?php echo tpl_form_field_date('timeend', !empty($item['timeend']) ? date('Y-m-d H:i',$item['timeend']) : date('Y-m-d H:i'), 1)?></div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span style='color:red'>*</span>分类</label>
    <div class="col-sm-4 col-xs-6">
        <select class="form-control" style="margin-right:15px;" id="pcate" name="pcate" onchange="fetchChildCategory(this.options[this.selectedIndex].value)"  autocomplete="off">
            <option value="0">请选择一级分类</option>
            <?php  if(is_array($category)) { foreach($category as $row) { ?>
            <?php  if($row['parentid'] == 0) { ?>
            <option value="<?php  echo $row['id'];?>" <?php  if($row['id'] == $item['pcate']) { ?> selected="selected"<?php  } ?>><?php  echo $row['name'];?></option>
            <?php  } ?>
            <?php  } } ?>
        </select>
    </div>
    <div class="col-sm-4 col-xs-6">
        <select class="form-control" id="cate_2" name="ccate" autocomplete="off">
            <option value="0">请选择二级分类</option>
            <?php  if(!empty($item['ccate']) && !empty($children[$item['pcate']])) { ?>
            <?php  if(is_array($children[$item['pcate']])) { foreach($children[$item['pcate']] as $row) { ?>
            <option value="<?php  echo $row['0'];?>" <?php  if($row['0'] == $item['ccate']) { ?> selected="selected"<?php  } ?>><?php  echo $row['1'];?></option>
            <?php  } } ?>
            <?php  } ?>
        </select>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品图</label>
    <div class="col-sm-9 col-xs-12">
        <?php  echo tpl_form_field_image('thumb', $item['thumb'], '', array('extras' => array('text' => 'readonly')))?>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">限时特价首页图</label>
    <div class="col-sm-9 col-xs-12">
        <?php  echo tpl_form_field_image('xsthumb',$item['xsthumb'])?>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">其他图片</label>
    <div class="col-sm-9 col-xs-12">
        <?php  echo tpl_form_field_multi_image('thumbs',$piclist)?>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品编号</label>
    <div class="col-sm-4 col-xs-12">
        <input type="text" name="goodssn" id="goodssn" class="form-control" value="<?php  echo $item['goodssn'];?>" />
    </div>
</div>
<div class="form-group">
    <label class=" col-sm-3 col-md-2 control-label">商品条码</label>
    <div class="col-sm-4 col-xs-12">
        <input type="text" name="productsn" id="productsn" class="form-control" value="<?php  echo $item['productsn'];?>" />
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品价格</label>
    <div class="col-sm-9 col-xs-12">
        <div class="input-group">
            <span class="input-group-addon">销售价</span>
            <input type="text" name="marketprice" id="marketprice" class="form-control" value="<?php  echo $item['marketprice'];?>" />
            <span class="input-group-addon">元</span>
        </div>
        <br>
        <div class="input-group">
            <span class="input-group-addon">市场价</span>
            <input type="text" name="productprice" id="productprice" class="form-control" value="<?php  echo $item['productprice'];?>" />
            <span class="input-group-addon">元</span>
        </div>
        <!--<br>
        <div class="input-group">
            <span class="input-group-addon">成本价</span>
            <input type="text" name="costprice" id="costprice" class="form-control" value="<?php  echo $item['costprice'];?>" />
            <span class="input-group-addon">元</span>
        </div>-->
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">重量</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="weight" id="weight" class="form-control" value="<?php  echo $item['weight'];?>" />
            <span class="input-group-addon">克</span>
        </div>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">库存</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="total" id="total" class="form-control" value="<?php  echo $item['total'];?>" />
            <span class="input-group-addon">件</span>
        </div>
        <span class="help-block">当前商品的库存数量，-1则表示不限制。</span>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">减库存方式</label>
    <div class="col-sm-9 col-xs-12">
        <label for="totalcnf1" class="radio-inline"><input type="radio" name="totalcnf" value="0" id="totalcnf1" <?php  if(empty($item) || $item['totalcnf'] == 0) { ?>checked="true"<?php  } ?> /> 拍下减库存</label>
        &nbsp;&nbsp;&nbsp;
        <label for="totalcnf2" class="radio-inline"><input type="radio" name="totalcnf" value="1" id="totalcnf2"  <?php  if(!empty($item) && $item['totalcnf'] == 1) { ?>checked="true"<?php  } ?> /> 付款减库存</label>
        &nbsp;&nbsp;&nbsp;
        <label for="totalcnf3" class="radio-inline"><input type="radio" name="totalcnf" value="2" id="totalcnf3"  <?php  if(!empty($item) && $item['totalcnf'] == 2) { ?>checked="true"<?php  } ?> /> 永不减库存</label>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">购买积分</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="credit" id="credit" class="form-control" value="<?php  echo $item['credit'];?>" />
            <span class="input-group-addon">分</span>
        </div>
        <p class="help-block">会员购买积分, 如果不填写，则默认为不奖励积分</p>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">运费</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="freight" id="freight" class="form-control" value="<?php  echo $item['freight'];?>" />
            <span class="input-group-addon">元</span>
        </div>
        <p class="help-block">商品运费，填-1自动填写为系统默认运费</p>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">满额包邮</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="free_shipping" id="free_shipping" class="form-control" value="<?php  echo $item['free_shipping'];?>" />
            <span class="input-group-addon">元</span>
        </div>
        <p class="help-block">满多少包邮</p>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">1级佣金比例</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="commission" id="commission" class="form-control" value="<?php  echo $item['commission'];?>" />
            <span class="input-group-addon">%</span>
        </div>
        <p class="help-block">1级佣金的比例,不填使用系统默认！</p>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">2级佣金比例</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="commission2" id="commission2" class="form-control" value="<?php  echo $item['commission2'];?>" />
            <span class="input-group-addon">%</span>
        </div>
        <p class="help-block">2级佣金的比例,不填使用系统默认！</p>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">3级佣金比例</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="commission3" id="commission3" class="form-control" value="<?php  echo $item['commission3'];?>" />
            <span class="input-group-addon">%</span>
        </div>
        <p class="help-block">3级佣金的比例,不填使用系统默认！</p>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">Property1</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="property1" id="property1" class="form-control" value="<?php  echo $item['property1'];?>" />
        </div>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">Property2</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <input type="text" name="property2" id="property2" class="form-control" value="<?php  echo $item['property2'];?>" />
        </div>
    </div>
</div>
<?php  if($item['id'] > 0 ) { ?>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">Add Property</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group">
            <table class="table table-hover">
                <thead class="navbar-inner">
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Property1</th>
                    <th>Property2</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php  if(is_array($property_list)) { foreach($property_list as $row) { ?>
                <tr>
                    <td>
                        <?php  echo $row['id'];?>
                    </td>
                    <td>
                        <?php  echo $row['property1_name'];?>
                    </td>
                    <td>
                        <?php  echo $row['property2_name'];?>
                    </td>
                    <td>
                        <?php  echo $row['stock'];?>
                    </td>
                    <td>
                        <button class="btn btn-default" onclick="delproperty(<?php  echo $row['id'];?>)"> Delete </button>
                    </td>
                </tr>
                <?php  } } ?>
                <tr>
                    <td></td>
                    <td>
                        <select class="form-control" id="property1_list" name="property1_list">
                            <option value="0">select Below</option>
                            <?php  if(is_array($property1_list)) { foreach($property1_list as $row) { ?>
                                <option value="<?php  echo $row['id'];?>"><?php  echo $row['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <select class="form-control" id="property2_list" name="property2_list">
                            <option value="0">select Below</option>
                            <?php  if(is_array($property2_list)) { foreach($property2_list as $row) { ?>
                            <option value="<?php  echo $row['id'];?>"><?php  echo $row['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="property_stock" id="property_stock" class="form-control"  />
                        <input type="hidden" name="property_attr" id="property_attr" class="form-control"  />
                        <input type="hidden" name="property_id" id="property_id" class="form-control"  />
                    </td>
                    <td>
                        <a class="btn btn-default" onclick="addproperty()"> Add </a>
                    </td>
                </tr>
                <tr>
                    <td> </td>
                    <td colspan="3"> <p id="property_message" style="color: red;"></p> </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  } ?>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否上架销售</label>
    <div class="col-sm-6 col-xs-12">
        <label for="isshow1" class="radio-inline"><input type="radio" name="status" value="1" id="isshow1" <?php  if($item['status'] == 1) { ?>checked="true"<?php  } ?> /> 是</label>
        <label for="isshow2" class="radio-inline"><input type="radio" name="status" value="0" id="isshow2"  <?php  if($item['status'] == 0) { ?>checked="true"<?php  } ?> /> 否</label>
    </div>
</div>


<script language="javascript">
    $('#property_attr').val('');
$(function(){

	var i = 0;
	$('#selectimage').click(function() {
		var editor = KindEditor.editor({
			allowFileManager : false,
			imageSizeLimit : '30MB',
			uploadJson : './index.php?act=attachment&do=upload'
		});
		editor.loadPlugin('multiimage', function() {
			editor.plugin.multiImageDialog({
				clickFn : function(list) {
					if (list && list.length > 0) {
						for (i in list) {
							if (list[i]) {
								html =	'<li class="imgbox" style="list-type:none">'+
								'<a class="item_close" href="javascript:;" onclick="deletepic(this);" title="删除"></a>'+
								'<span class="item_box"> <img src="'+list[i]['url']+'" style="height:80px"></span>'+
								'<input type="hidden" name="attachment-new[]" value="'+list[i]['filename']+'" />'+
								'</li>';
								$('#fileList').append(html);
								i++;
							}
						}
						editor.hideDialog();
					} else {
						alert('请先选择要上传的图片！');
					}
				}
			});
		});
	});
});
function deletepic(obj){
	if (confirm("确认要删除？")) {
		var $thisob=$(obj);
		var $liobj=$thisob.parent();
		var picurl=$liobj.children('input').val();
		$.post('<?php  echo $this->createMobileUrl('ajaxdelete',array())?>',{ pic:picurl},function(m){
			if(m=='1') {
				$liobj.remove();
			} else {
				alert("删除失败");
			}
		},"html");
	}
}

function addproperty() {
    $('#property_attr').val('add');
    var property1  = $('#property1_list').val();
    var property2  = $('#property2_list').val();
    var stock      = $('#property_stock').val();
    if(property1 == '0'){
        $('#property_message').text("Please enter Property1.");
        return false;
    }
    if(property2 == '0'){
        $('#property_message').text("Please enter Property2.");
        return false;
    }
    if(stock == ''){
        $('#property_message').text("Please enter stock.");
        return false;
    }
    var total = $('#total').val();
    if(parseInt(stock) > parseInt(total)) {
        $('#property_message').text("Stock can't big than stock of total.");
        return false;
    }
    $('#property_message').text("Please click save button.");
    return true;
}
function delproperty(id) {
    $('#property_attr').val('del');
    $('#property_id').val(id);
    this.submit();
}

    </script>