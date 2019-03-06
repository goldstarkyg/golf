<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}

class List_EweiShopV2Page extends PluginWebPage
{
	public function main()
	{
		global $_W , $_GPC;

//		$pindex = max(1, intval($_GPC['page']));
//		$psize = 10;
//		$sql = "SELECT * FROM tablename('ewei_shop_offline_order') WHERE uniacid = {$_W['uniacid']}  limit' .  (($pindex - 1) * $psize) . ',' . $psize; ";
//
//		$list = pdo_fetchall($sql, $params);
//		$total = pdo_fetchcolumn('select count(dm.id) from' . tablename('ewei_shop_member') . ' dm  ' . ' left join ' . tablename('ewei_shop_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('ewei_shop_abonus_level') . ' l on l.id = dm.aagentlevel' . ' where dm.uniacid =' . $_W['uniacid'] . ' and dm.isaagent =1  ' . $condition, $params);
//
//
//
//
//
//
//		$pager = pagination($total, $pindex, $psize);
		load()->func('tpl');
		include $this->template();
	}








}


?>