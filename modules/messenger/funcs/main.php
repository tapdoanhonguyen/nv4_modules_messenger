<?php

/**
 * TMS Content Management System
 * @version 4.x
 * @author Tập Đoàn TMS Holdings <contact@tms.vn>
 * @copyright (C) 2009-2021 Tập Đoàn TMS Holdings. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://tms.vn
 */

if (!defined('NV_IS_MOD_MESSENGER')) die('Stop!!!');

$array_data = array();

$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
$per_page = $array_config['per_page'];

$db->sqlreset()
    ->select('COUNT(*)')
    ->from(NV_PREFIXLANG . '_' . $module_data . '_topic t1')
    ->join('INNER JOIN ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users t2 ON t1.id=t2.topicid')
    ->where('t2.userid=' . $user_info['userid']);

$sth = $db->prepare($db->sql());

$sth->execute();
$num_items = $sth->fetchColumn();

$db->select('t1.*, t2.viewed')
    ->order('t1.reply_time DESC')
    ->limit($per_page)
    ->offset(($page - 1) * $per_page);
$sth = $db->prepare($db->sql());

$sth->execute();

while ($_row = $sth->fetch()) {
    $array_data[] = nv_show_data($_row);
}

$generate_page = nv_alias_page($page_title, $base_url, $num_items, $per_page, $page);
$contents = nv_theme_messenger_main($array_data, $generate_page);

$page_title = $module_info['custom_title'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';