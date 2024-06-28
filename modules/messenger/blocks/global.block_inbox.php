<?php

/**
 * TMS Content Management System
 * @version 4.x
 * @author Tập Đoàn TMS Holdings <contact@tms.vn>
 * @copyright (C) 2009-2021 Tập Đoàn TMS Holdings. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://tms.vn
 */
if (!defined('NV_MAINFILE')) die('Stop!!!');

if (!nv_function_exists('nv_messenger_inbox')) {

    function nv_block_config_messenger_inbox($module, $data_block, $lang_block)
    {
        global $site_mods, $global_config;

        if (file_exists(NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $site_mods[$module]['module_file'] . '/block_inbox.tpl')) {
            $block_theme = $global_config['module_theme'];
        } else {
            $block_theme = 'default';
        }

        $xtpl = new XTemplate('block_inbox.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/' . $site_mods[$module]['module_file']);
        $xtpl->assign('LANG', $lang_block);
        $xtpl->assign('DATA', $data_block);

        $xtpl->parse('config');
        return $xtpl->text('config');
    }

    function nv_block_config_messenger_inbox_submit($module, $lang_block)
    {
        global $nv_Request;
        $return = array();
        $return['error'] = array();
        $return['config'] = array();
        $return['config']['numrow'] = $nv_Request->get_int('config_numrow', 'post', 5);
        $return['config']['title_length'] = $nv_Request->get_int('config_title_length', 'post', 0);
        return $return;
    }

    function nv_messenger_inbox($block_config)
    {
        global $db, $module_info, $lang_module, $global_config, $site_mods, $module_name, $my_head, $user_info;

        if (!defined('NV_IS_USER')) {
            return '';
        }

        $module = $block_config['module'];
        $mod_file = $site_mods[$module]['module_file'];
        $mod_data = $site_mods[$module]['module_data'];
        $array_data = array();

        if (file_exists(NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $mod_file . '/block_inbox.tpl')) {
            $block_theme = $global_config['module_theme'];
        } else {
            $block_theme = 'default';
        }

        if ($module != $module_name) {
            require_once NV_ROOTDIR . '/modules/' . $mod_file . '/language/' . NV_LANG_INTERFACE . '.php';
            $my_head .= '<link rel="StyleSheet" href="' . NV_BASE_SITEURL . 'themes/' . $block_theme . '/css/messenger.css">';
        }

        $db->sqlreset()
            ->select('t1.*, t2.viewed')
            ->from(NV_PREFIXLANG . '_' . $mod_data . '_topic t1')
            ->join('INNER JOIN ' . NV_PREFIXLANG . '_' . $mod_data . '_topic_users t2 ON t1.id=t2.topicid')
            ->where('t2.userid=' . $user_info['userid'])
            ->order('t1.reply_time DESC')
            ->limit($block_config['numrow']);

        $sth = $db->prepare($db->sql());
        $sth->execute();

        $array_users = array();
        while ($_row = $sth->fetch()) {
            if (!isset($array_users[$_row['userid']])) {
                $user = $db->query('SELECT username, photo FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $_row['userid'])->fetch();
                $array_users[$_row['userid']] = $user;
            } else {
                $user = $array_users[$_row['userid']];
            }

            if (file_exists(NV_ROOTDIR . '/' . $user['photo']) and !empty($user['photo'])) {
                $_row['avata'] = NV_BASE_SITEURL . $user['photo'];
            } else {
                $_row['avata'] = NV_BASE_SITEURL . 'themes/' . $module_info['template'] . '/images/users/no_avatar.png';
            }

            $_row['username'] = $user['username'];
            $_row['addtime'] = nv_date('H:i d/m/Y', $_row['addtime']);
            $_row['link_view'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . change_alias($_row['title']) . '-' . $_row['id'];

            $array_data[] = $_row;
        }

        $xtpl = new XTemplate('block_inbox.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/' . $mod_file);
        $xtpl->assign('LANG', $lang_module);
        $xtpl->assign('URL_VIEWALL', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module);
        $xtpl->assign('URL_TOPIC_ADD', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $site_mods[$module]['alias']['content']);

        if (!empty($array_data)) {
            foreach ($array_data as $data) {
                $data['title0'] = nv_clean60($data['title'], $block_config['title_length']);
                $xtpl->assign('DATA', $data);
                if (!$data['viewed']) {
                    $xtpl->parse('main.loop.unread');
                }
                $xtpl->parse('main.loop');
            }
        }

        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}

if (defined('NV_SYSTEM')) {
    global $site_mods, $module_name, $nv_Cache;

    $module = $block_config['module'];

    if (isset($site_mods[$module])) {
        $content = nv_messenger_inbox($block_config);
    }
}
