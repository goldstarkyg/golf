<?php
if (!defined('IN_IA')) 
{
	exit('Access Denied');
}
class Lists_EweiShopV2Page extends PluginMobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$member = m('member')->getMember($openid);
		$shop = m('common')->getSysset('shop');
		$uniacid = $_W['uniacid'];
		$cateid = intval($_GPC['cate']);
		if (!empty($cateid)) 
		{
			$cate = pdo_fetch('select id,name from ' . tablename('ewei_shop_creditshop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cateid, ':uniacid' => $uniacid));
		}
		$_W['shopshare'] = array('title' => $this->set['share_title'], 'imgUrl' => tomedia($this->set['share_icon']), 'link' => mobileUrl('creditshop', array(), true), 'desc' => $this->set['share_desc']);
		$com = p('commission');
		if ($com) 
		{
			$cset = $com->getSet();
			if (!empty($cset)) 
			{
				if (($member['isagent'] == 1) && ($member['status'] == 1)) 
				{
					$_W['shopshare']['link'] = mobileUrl('creditshop', array('mid' => $member['id']), true);
					if (empty($cset['become_reg']) && (empty($member['realname']) || empty($member['mobile']))) 
					{
						$trigger = true;
					}
				}
				else if (!empty($_GPC['mid'])) 
				{
					$_W['shopshare']['link'] = mobileUrl('creditshop/detail', array('mid' => $_GPC['mid']), true);
				}
			}
		}
		include $this->template();
	}
	public function getlist() 
	{
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$member = m('member')->getMember($openid);
		$shop = m('common')->getSysset('shop');
		$uniacid = $_W['uniacid'];
		$cateid = intval($_GPC['cate']);
		$cate = pdo_fetch('select id,name from ' . tablename('ewei_shop_creditshop_category') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $cateid, ':uniacid' => $uniacid));
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = ' and uniacid = :uniacid and status=1 and deleted=0';
		$params = array(':uniacid' => $_W['uniacid']);
		if (!empty($cate)) 
		{
			$condition .= ' and cate=' . $cateid;
		}
		$keyword = trim($_GPC['keyword']);
		if (!empty($keyword)) 
		{
			$condition .= ' AND title like \'%' . $keyword . '%\' ';
		}
		$sql = 'SELECT COUNT(*) FROM ' . tablename('ewei_shop_creditshop_goods') . ' where 1 ' . $condition;
		$total = pdo_fetchcolumn($sql, $params);
		$list = array();
		if (!empty($total)) 
		{
			$sql = 'SELECT id,title,thumb,subtitle,type,credit,money FROM ' . tablename('ewei_shop_creditshop_goods') . '  where 1 ' . $condition . ' ORDER BY displayorder desc,id DESC LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
			$list = pdo_fetchall($sql, $params);
			$list = set_medias($list, 'thumb');
			foreach ($list as &$row ) 
			{
				if ((0 < $row['credit']) & (0 < $row['money'])) 
				{
					$row['acttype'] = 0;
				}
				else if (0 < $row['credit']) 
				{
					$row['acttype'] = 1;
				}
				else if (0 < $goods['money']) 
				{
					$row['acttype'] = 2;
				}
			}
			unset($row);
		}
		show_json(1, array('list' => $list, 'pagesize' => $psize, 'total' => $total));
	}
}
?>