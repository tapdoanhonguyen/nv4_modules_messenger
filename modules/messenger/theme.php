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

/**
 * nv_theme_messenger_main()
 *
 * @param mixed $array_data
 * @param mixed $generate_page
 * @return
 *
 */
function nv_theme_messenger_main($array_data, $generate_page = '')
{
    global $lang_global, $global_config, $module_name, $module_file, $lang_module, $module_config, $module_info, $op;

    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('URL_ADDTOPIC', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $module_info['alias']['content']);

    if (!empty($array_data)) {
        if (!empty($array_data)) {
            foreach ($array_data as $data) {
                $data['addtime'] = nv_date('H:i d/m/Y', $data['addtime']);
                $xtpl->assign('DATA', $data);

                if (!$data['viewed']) {
                    $xtpl->parse('main.loop.unread');
                }

                $xtpl->parse('main.loop');
            }
        }

        if (!empty($generate_page)) {
            $xtpl->assign('PAGE', $generate_page);
            $xtpl->parse('main.page');
        }
    }

    $array_action = array(
        'delete_list_id' => $lang_global['delete']
    );
    foreach ($array_action as $key => $value) {
        $xtpl->assign('ACTION', array(
            'key' => $key,
            'value' => $value
        ));
        $xtpl->parse('main.action');
    }

    $xtpl->parse('main');
    return $xtpl->text('main');
}

/**
 * nv_theme_messenger_content()
 *
 * @param mixed $row
 * @param mixed $error
 * @return
 *
 */
function nv_theme_messenger_content($row, $error)
{
    global $global_config, $module_name, $module_file, $lang_module, $module_config, $module_info, $op, $module_upload, $array_config, $groups_list, $user_info;

    $xtpl = new XTemplate('content.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('TEMPLATE', $module_info['template']);
    $xtpl->assign('ROW', $row);
    $xtpl->assign('EDITOR', $array_config['editor']);

    if (nv_use_captcha()) {
        $xtpl->parse('main.captcha');
    }

    if (!empty($row['id']) and !empty($row['list_username'])) {
        foreach ($row['list_username'] as $user) {
            $xtpl->assign('USER', $user);
            $xtpl->parse('main.user');
        }
    }

    $array_config['groups_topic_group'] = !empty($array_config['groups_topic_group']) ? array_map('intval', explode(',', $array_config['groups_topic_group'])) : array();
    $check = array_intersect($array_config['groups_topic_group'], $user_info['in_groups']);
    if (!empty($check) and !empty($array_config['groups_topic'])) {
        $array_config['groups_topic'] = explode(',', $array_config['groups_topic']);
        foreach ($array_config['groups_topic'] as $value) {
            $xtpl->assign('GROUPS_TOPIC', array(
                'index' => $value,
                'value' => $groups_list[$value],
                'checked' => in_array($value, $row['list_groups']) ? 'checked="checked"' : ''
            ));
            $xtpl->parse('main.groups_topic.loop');
        }
        $xtpl->parse('main.groups_topic');
    }

    if (!empty($error)) {
        $xtpl->assign('ERROR', implode('<br />', $error));
        $xtpl->parse('main.error');
    }

    $xtpl->parse('main');
    return $xtpl->text('main');
}

/**
 * nv_theme_messenger_viewtopic()
 *
 * @param mixed $topic_info
 * @param mixed $array_reply
 * @param mixed $reply_content
 * @param mixed $array_topic_users
 * @param mixed $generate_page
 * @return
 *
 */
function nv_theme_messenger_viewtopic($topic_info, $array_reply, $reply_content, $array_topic_users, $generate_page)
{
    global $global_config, $module_name, $module_file, $lang_module, $module_info, $op, $array_config, $user_info, $themeConfig, $groups_list;

    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('TEMPLATE', $module_info['template']);
    $xtpl->assign('TOPIC', $topic_info);
    $xtpl->assign('TOPIC_ITEM', nv_theme_messenger_reply_item($topic_info, 1));
    $xtpl->assign('REPLY_CONTENT', $reply_content);
    $xtpl->assign('EDITOR', $array_config['editor']);

    if (!empty($topic_info['list_groups'])) {
        $topic_info['list_groups'] = explode(',', $topic_info['list_groups']);
        $i = 1;
        foreach ($topic_info['list_groups'] as $group_id) {
            $xtpl->assign('GROUP', array(
                'number' => $i,
                'title' => $groups_list[$group_id]
            ));
            $xtpl->parse('main.groups.loop');
            $i++;
        }
        $xtpl->parse('main.groups');
    }

    if (!empty($array_reply)) {
        foreach ($array_reply as $reply) {
            $xtpl->assign('REPLY_ITEM', nv_theme_messenger_reply_item($reply));
            $xtpl->parse('main.loop');
        }
    }

    foreach ($array_topic_users as $_user_info) {
        $xtpl->assign('USER', $_user_info);
        if ($_user_info['userid'] == $user_info['userid']) {
            $xtpl->parse('main.user.isyou');
        }
        $xtpl->parse('main.user');
    }

    if (nv_use_captcha()) {
        $xtpl->parse('main.frm_reply.captcha');
    }

    $xtpl->parse('main.frm_reply');

    $_posAllowed = array();
    if (isset($themeConfig['positions']) && !empty($themeConfig['positions']['position'])) {
        foreach ($themeConfig['positions']['position'] as $_pos) {
            $_pos = trim((string) $_pos['tag']);
            unset($matches);
            if (preg_match('/^\[([^\]]+)\]$/is', $_pos, $matches)) {
                $_posAllowed[] = $matches[1];
            }
        }
    }

    if (!empty($generate_page)) {
        $xtpl->assign('PAGE', $generate_page);
        $xtpl->parse('main.page');
    }

    $xtpl->parse('main');
    return $xtpl->text('main');
}

/**
 * nv_theme_messenger_reply_item()
 *
 * @param mixed $array_data
 * @return
 *
 */
function nv_theme_messenger_reply_item($array_data, $is_topic = 0)
{
    global $global_config, $module_name, $module_file, $lang_module, $module_config, $module_info, $op, $lang_global, $user_info;

    $array_data['is_topic'] = $is_topic;

    $xtpl = new XTemplate('replyitem.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('GLANG', $lang_global);
    $xtpl->assign('DATA', $array_data);
    $xtpl->assign('TEMPLATE', $module_info['template']);

    if ($array_data['userid'] == $user_info['userid']) {
        $xtpl->parse('main.edit');
        $xtpl->parse('main.delete');
    }

    $xtpl->parse('main');
    return $xtpl->text('main');
}

/**
 * nv_theme_messenger_search()
 *
 * @param mixed $array_data
 * @return
 *
 */
function nv_theme_messenger_search($array_data)
{
    global $global_config, $module_name, $module_file, $lang_module, $module_config, $module_info, $op;

    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);

    $xtpl->parse('main');
    return $xtpl->text('main');
}
