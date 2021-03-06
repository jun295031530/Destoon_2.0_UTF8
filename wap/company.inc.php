<?php
/*
	[Destoon B2B System] Copyright (c) 2009 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
$AREA = cache_read('area.php');
$table = $DT_PRE.'company';
$userid = isset($userid) ? intval($userid) : 0;
$username = isset($username) ? trim($username) : '';
if($userid || $username) {
	$sql = $userid ? "userid=$userid" : "username='$username'";
	$item = $db->get_one("SELECT * FROM {$table} WHERE $sql");
	$item or wap_msg('公司不存在');
	extract($item);
	
	$could_contact = check_group($_groupid, $MOD['group_contact']);
	if($username == $_username) $could_contact = true;

	$content = $db->get_one("SELECT content FROM {$DT_PRE}company_data WHERE userid=$userid");
	$content = $content['content'];
	$content = strip_tags($content);
	$content = preg_replace("/\&([^;]+);/i", '', $content);
	$contentlength = strlen($content);
	if($contentlength > $maxlength) {
		$start = ($page-1)*$maxlength;
		$content = dsubstr($content, $maxlength, '', $start);
		$pages = wap_pages($contentlength, $page, $maxlength);
	}
	$content = nl2br($content);
	if($page == 1) $db->query("UPDATE {$table} SET hits=hits+1 WHERE userid=$userid");
	$head_title = $company.$DT['seo_delimiter'].$MOD['name'].$DT['seo_delimiter'].$head_title;
} else {
	if($kw) {
		check_group($_groupid, $MOD['group_search']) or wap_msg('无权搜索');
	} else if($catid) {
		isset($CATEGORY[$catid]) or wap_msg('分类不存在');
		$CAT = cache_read('category_'.$catid.'.php');
		if(!check_group($_groupid, $MOD['group_list']) || !check_group($_groupid, $CAT['group_list'])) {
			wap_msg('无权浏览的页面');
		}
	} else {
		check_group($_groupid, $MOD['group_index']) or wap_msg('无权浏览的页面');
	}

	$head_title = $MOD['name'].$DT['seo_delimiter'].$head_title;
	if($kw) $head_title = $kw.$DT['seo_delimiter'].$head_title;
	$keyword = $kw ? str_replace(array(' ', '*'), array('%', '%'), $kw) : '';
	$condition = "groupid>5";
	if($keyword) $condition .= " AND keyword LIKE '%$keyword%'";
	if($areaid) $condition .= ($AREA[$areaid]['child']) ? " AND areaid IN (".$AREA[$areaid]['arrchildid'].")" : " AND areaid=$areaid";
	$r = $db->get_one("SELECT COUNT(userid) AS num FROM {$table} WHERE $condition");
	$pages = wap_pages($r['num'], $page, $pagesize);
	$lists = array();
	$order = $MOD['order'];
	$result = $db->query("SELECT userid,catid,company,areaid,vip FROM {$table} WHERE $condition ORDER BY $order LIMIT $offset,$pagesize");
	while($r = $db->fetch_array($result)) {
		$r['area'] = area_pos($r['areaid'], '/', 2);
		$lists[] = $r;
	}
}
include template('company', 'wap');
?>