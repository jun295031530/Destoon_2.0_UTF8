<?php
defined('IN_DESTOON') or exit('Access Denied');
$all = (isset($all) && $all) ? 1 : 0;
$menus = array (
    array('生成网页', '?moduleid='.$moduleid.'&file='.$file),
    array('一键生成', '?moduleid='.$moduleid.'&file='.$file.'&action=all'),
);
$all = (isset($all) && $all) ? 1 : 0;
$one = (isset($one) && $one) ? 1 : 0;
$this_forward = '?moduleid='.$moduleid.'&file='.$file;
$MOD['show_html'] = 0;
switch($action) {
	case 'all':
		msg('', '?moduleid='.$moduleid.'&file='.$file.'&action=show&update=1&all=1&one='.$one);
	break;
	case 'index':
		tohtml('index', $module);
		$all ? msg($MOD['name'].'首页生成成功', '?moduleid='.$moduleid.'&file='.$file.'&action=list&all=1&one='.$one) : dmsg($MOD['name'].'首页生成成功', $this_forward);
	break;
	case 'list':
		if(!$MOD['list_html']) {
			$all ? msg($MOD['name'].'列表生成成功', '?moduleid='.$moduleid.'&file='.$file.'&action=show&all='.$all.'&one='.$one) : msg($MOD['name'].'列表生成成功', $this_forward);
		}
		if(isset($catids)) {
			$KEYS = array_keys($CATEGORY);
			if(isset($KEYS[$catids])) {
				$bcatid = $catid = $KEYS[$catids];
				tohtml('list', $module);
				msg($MOD['name'].' ['.$CATEGORY[$bcatid]['catname'].'] 生成成功', '?moduleid='.$moduleid.'&file='.$file.'&action='.$action.'&catids='.($catids+1).'&all='.$all.'&one='.$one);
			} else {
				$all ? msg($MOD['name'].'列表生成成功', '?moduleid='.$moduleid.'&file='.$file.'&action=show&all='.$all.'&one='.$one) : msg($MOD['name'].'列表生成成功', $this_forward);
			}		
		} else {
			$catids = 0;
			msg('', '?moduleid='.$moduleid.'&file='.$file.'&action='.$action.'&catids='.$catids.'&all='.$all.'&one='.$one);
		}
	break;
	case 'show':
		$update = (isset($update) && $update) ? 1 : 0;
		if(!$update && !$MOD['show_html']) {
			if($one) msg('', '?file=html&action=back&mid='.$moduleid);
			$all ? msg($MOD['name'].'生成成功', $this_forward) : dmsg($MOD['name'].'生成成功', $this_forward);
		}
		$catid = isset($catid) ? intval($catid) : '';
		$sql = $catid ? " AND catid=$catid" : '';
		if(!isset($fid)) {
			$r = $db->get_one("SELECT min(userid) AS fid FROM {$table} WHERE groupid>4 {$sql}");
			$fid = $r['fid'] ? $r['fid'] : 0;
		}
		if(!isset($tid)) {
			$r = $db->get_one("SELECT max(userid) AS tid FROM {$table} WHERE groupid>4 {$sql}");
			$tid = $r['tid'] ? $r['tid'] : 0;
		}
		if($update) {
			require  MOD_ROOT.'/company.class.php';
			$do = new company($moduleid);
		}
		isset($num) or $num = 50;
		if($fid <= $tid) {
			$result = $db->query("SELECT userid FROM {$table} WHERE groupid>4 AND userid>=$fid {$sql} ORDER BY userid LIMIT 0,$num ");
			if($db->affected_rows($result)) {
				while($r = $db->fetch_array($result)) {
					$userid = $r['userid'];
					$update ? $do->update($userid) : tohtml('show', $module);
				}
				$userid += 1;
			} else {
				$userid = $fid + $num;
			}
		} else {
			if($update) {
				$all ? msg('', '?moduleid='.$moduleid.'&file='.$file.'&action=index&all=1&one='.$one) : dmsg('更新成功', $this_forward);
			} else {
				if($one) msg($MOD['name'].'生成成功', '?file=html&action=back&mid='.$moduleid);
				$all ? msg($MOD['name'].'生成成功', $this_forward) : dmsg($MOD['name'].'生成成功', $this_forward);
			}
		}
		msg('ID从'.$fid.'至'.($userid-1).$MOD['name'].($update ? '更新' : '生成').'成功', "?moduleid=$moduleid&file=$file&action=$action&fid=$userid&tid=$tid&num=$num&update=$update&all=$all&one=$one");
	break;
	case 'cate':
		$catid or msg('请选择分类');
		if(!isset($item_count)) {
			$condition = $CATEGORY[$catid]['child'] ? "catid IN (".$CATEGORY[$catid]['arrchildid'].")" : "catid=$catid";
			$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE $condition AND groupid>4");
			$item_count = $r['num'];
			cache_item($moduleid, $catid, $item_count);
		}
		isset($num) or $num = 50;
		isset($fid) or $fid = 1;
		$total = ceil($item_count/$MOD['pagesize']);
		$total = $total ? $total : 1;
		if($fpage && $tpage) {
			$fpage = 1;
			$num = $tpage - $fpage;
			tohtml('list', $module);
			dmsg('生成成功', $this_forward);
		}
		if($fid <= $total) {
			tohtml('list', $module);
			msg('第'.$fid.'页至第'.($fid+$num-1).'页生成成功', '?moduleid='.$moduleid.'&file='.$file.'&action='.$action.'&catid='.$catid.'&fid='.($fid+$num).'&num='.$num.'&item_count='.$item_count);
		} else {
			dmsg('生成成功', $this_forward);
		}
	break;
	default:
		$r = $db->get_one("SELECT min(userid) AS fid,max(userid) AS tid FROM {$table} WHERE groupid>4");
		$fid = $r['fid'] ? $r['fid'] : 0;
		$tid = $r['tid'] ? $r['tid'] : 0;
		include tpl('html', $module);
	break;
}
?>