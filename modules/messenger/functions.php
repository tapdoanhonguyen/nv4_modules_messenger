<?php

/**
 * TMS Content Management System
 * @version 4.x
 * @author Tập Đoàn TMS Holdings <contact@tms.vn>
 * @copyright (C) 2009-2021 Tập Đoàn TMS Holdings. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://tms.vn
 */
if (!defined('NV_SYSTEM')) die('Stop!!!');

if (!defined('NV_IS_USER')) {
    Header('Location: ' . NV_BASE_SITEURL . 'index.php?' . NV_NAME_VARIABLE . '=users&' . NV_OP_VARIABLE . '=login&nv_redirect=' . nv_redirect_encrypt($client_info['selfurl']));
    die();
}

define('NV_IS_MOD_MESSENGER', true);
require_once NV_ROOTDIR . '/modules/' . $module_file . '/global.functions.php';

$page = 1;
$per_page = 20;
$topicid = 0;

if ($op == 'main') {
    if (sizeof($array_op) == 1) {
        if (preg_match('/^page\-([0-9]+)$/', (isset($array_op[0]) ? $array_op[0] : ''), $m)) {
            $page = (int) $m[1];
        } elseif (preg_match('/^([a-z0-9\-]+)\-([0-9]+)$/i', $array_op[0], $m1) and !preg_match('/^page\-([0-9]+)$/', $array_op[0], $m2)) {
            $op = 'viewtopic';
            $topicid = $m1[2];
        }
    }
}

/**
 * reply_result()
 *
 * @param mixed $array
 * @return
 *
 */
function reply_result($array)
{
    $string = json_encode($array);
    return $string;
}

function nv_use_captcha()
{
    global $array_config;

    if ($array_config['captcha'] == 3 or ($array_config['captcha'] == 2 and !defined('NV_IS_MODADMIN')) or ($array_config['captcha'] == 1 and !defined('NV_IS_USER'))) {
        return true;
    }

    return false;
}

function nv_show_data($row)
{
    global $db, $module_info, $module_name, $array_cat;

    $user = $db->query('SELECT username, photo FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $row['userid'])->fetch();
    if (file_exists(NV_ROOTDIR . '/' . $user['photo']) and !empty($user['photo'])) {
        $row['avata'] = NV_BASE_SITEURL . $user['photo'];
    } else {
        $row['avata'] = NV_BASE_SITEURL . 'themes/' . $module_info['template'] . '/images/users/no_avatar.png';
    }
    $row['username'] = $user['username'];
    $row['link_view'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . change_alias($row['title']) . '-' . $row['id'];

    return $row;
}

function nv_get_topic_users($topicid, $group = 0)
{
    global $db, $module_data, $module_info;

    $array_userid = $array_user_info = array();
    $where = '';

    if (empty($group)) {
        $where = ' AND group_id=0';
    }

    // danh sách userid tham gia hội thoại
    $result = $db->query('SELECT userid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users WHERE topicid=' . $topicid . $where);
    while (list ($userid) = $result->fetch(3)) {
        $array_userid[] = $userid;
    }
    $array_userid = array_unique($array_userid);

    if (!empty($array_userid)) {
        $result = $db->query('SELECT userid, first_name, last_name, photo, email, username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid IN (' . implode(',', $array_userid) . ')');
        while ($row = $result->fetch()) {
            $row['fullname'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);

            if (file_exists(NV_ROOTDIR . '/' . $row['photo']) and !empty($row['photo'])) {
                $row['photo'] = NV_BASE_SITEURL . $row['photo'];
            } else {
                $row['photo'] = NV_BASE_SITEURL . 'themes/' . $module_info['template'] . '/images/users/no_avatar.png';
            }

            $array_user_info[$row['userid']] = $row;
        }
    }

    return $array_user_info;
}

/**
 * nv_groups_list()
 *
 * @param string $mod_data
 * @return
 *
 */
function nv_groups_list($mod_data = 'users')
{
    global $nv_Cache;
    $cache_file = NV_LANG_DATA . '_groups_list_' . NV_CACHE_PREFIX . '.cache';
    if (($cache = $nv_Cache->getItem($mod_data, $cache_file)) != false) {
        return unserialize($cache);
    }
    global $db, $db_config, $global_config, $lang_global;

    $groups = [];
    $_mod_table = ($mod_data == 'users') ? NV_USERS_GLOBALTABLE : $db_config['prefix'] . '_' . $mod_data;
    $result = $db->query('SELECT g.group_id, d.title, g.idsite FROM ' . $_mod_table . '_groups AS g LEFT JOIN ' . $_mod_table . "_groups_detail d ON ( g.group_id = d.group_id AND d.lang='" . NV_LANG_DATA . "' ) WHERE (g.idsite = " . $global_config['idsite'] . ' OR (g.idsite =0 AND g.siteus = 1)) ORDER BY g.idsite, g.weight');
    while ($row = $result->fetch()) {
        if ($row['group_id'] < 9) {
            $row['title'] = $lang_global['level' . $row['group_id']];
        }
        $groups[$row['group_id']] = ($global_config['idsite'] > 0 and empty($row['idsite'])) ? '<strong>' . $row['title'] . '</strong>' : $row['title'];
    }
    $nv_Cache->setItem($mod_data, $cache_file, serialize($groups));

    return $groups;
}
