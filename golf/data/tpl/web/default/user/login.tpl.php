<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header-base', TEMPLATE_INCLUDEPATH)) : (include template('common/header-base', TEMPLATE_INCLUDEPATH));?>
<style>
	body{
		overflow: hidden;
	}
	@media screen and (max-width:767px){.login .panel.panel-default{width:80%; min-width:300px;}}
	@media screen and (min-width:768px){.login .panel.panel-default{width:60%;}}
	@media screen and (min-width:1200px){.login .panel.panel-default{width:40%;}}
</style>
<div class="login">
	<div class="logo">
		<a href="./?refresh" <?php  if(!empty($_W['setting']['copyright']['flogo'])) { ?>style="background:url('<?php  echo tomedia($_W['setting']['copyright']['flogo']);?>') no-repeat;"<?php  } ?>></a>
	</div>
	<div class="clearfix" style="margin-bottom:0em;">
		<div class="panel panel-default container">
			<div class="panel-body">
				<form action="" method="post" role="form" id="form1" onsubmit="return formcheck();">
					<div class="form-group input-group">
						<span id="message" class="text-danger"></span>
					</div>
					<div class="form-group input-group">
						<div class="input-group-addon"><i class="fa fa-user"></i></div>
						<input name="username" type="text" class="form-control input-lg" placeholder="请输入用户名登录">
					</div>
					<div class="form-group input-group">
						<div class="input-group-addon"><i class="fa fa-unlock-alt"></i></div>
						<input name="password" type="password" class="form-control input-lg" placeholder="请输入登录密码">
					</div>
					<?php  if(!empty($_W['setting']['copyright']['verifycode'])) { ?>
					<div class="form-group input-group">
						<div class="input-group-addon"><i class="fa fa-info"></i></div>
						<input name="verify" type="text" class="form-control input-lg" style="width:200px;" placeholder="请输入验证码">
						<a href="javascript:;" id="toggle" style="text-decoration: none"><img id="imgverify" src="<?php  echo url('utility/code')?>" style="height:46px;" title="点击图片更换验证码"/> 看不清？换一张</a>
					</div>
					<?php  } ?>
					<div class="form-group">
						<label class="checkbox-inline input-lg">
							<input type="checkbox" value="true" name="rember"> 记住用户名
						</label>
						<div class="pull-right">
							<?php  if(!$_W['siteclose']) { ?><a href="<?php  echo url('user/register');?>" class="btn btn-link btn-lg">注册</a><?php  } ?>
							<input type="submit" id="submit"  name="submit"  value="登录" class="btn btn-primary btn-lg" />
							<input name="token" value="<?php  echo $_W['token'];?>" type="hidden" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

</div>
<script>
function formcheck() {
	if($('#remember:checked').length == 1) {
		cookie.set('remember-username', $(':text[name="username"]').val());
	} else {
		cookie.del('remember-username');
	}
	return true;
}
var h = document.documentElement.clientHeight;
$(".login").css('min-height',h);
$('#toggle').click(function() {
	$('#imgverify').prop('src', '<?php  echo url('utility/code')?>r='+Math.round(new Date().getTime()));
	return false;
});
<?php  if(!empty($_W['setting']['copyright']['verifycode'])) { ?>
	$('#form1').submit(function() {
		var verify = $(':text[name="verify"]').val();
		if (verify == '') {
			alert('请填写验证码');
			return false;
		}
	});
<?php  } ?>
</script>
</body>
</html>
