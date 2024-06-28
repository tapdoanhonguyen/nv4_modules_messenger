<?php

/**
 * TMS Content Management System
 * @version 4.x
 * @author Tập Đoàn TMS Holdings <contact@tms.vn>
 * @copyright (C) 2009-2021 Tập Đoàn TMS Holdings. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://tms.vn
 */
if (!defined('NV_IS_FILE_ADMIN')) die('Stop!!!');

$page_title = $lang_module['config'];
$groups_list = nv_groups_list();

if ($nv_Request->isset_request('savesetting', 'post')) {
    $data['per_page'] = $nv_Request->get_int('per_page', 'post', 15);
    $data['per_topic'] = $nv_Request->get_int('per_topic', 'post', 15);
    $data['captcha'] = $nv_Request->get_int('captcha', 'post', 0);
    $data['infoemail'] = $nv_Request->get_int('infoemail', 'post', 0);

    $data['groups_topic'] = $nv_Request->get_typed_array('groups_topic', 'post', 'int', array());
    $data['groups_topic'] = !empty($data['groups_topic']) ? implode(',', $data['groups_topic']) : '';

    $data['groups_topic_group'] = $nv_Request->get_typed_array('groups_topic_group', 'post', 'int', array());
    $data['groups_topic_group'] = !empty($data['groups_topic_group']) ? implode(',', $data['groups_topic_group']) : '';

    $_groups_post = $nv_Request->get_array('groups_post', 'post', array());
    $data['groups_post'] = !empty($_groups_post) ? implode(',', nv_groups_post(array_intersect($_groups_post, array_keys($groups_list)))) : '';

    $sth = $db->prepare("UPDATE " . NV_CONFIG_GLOBALTABLE . " SET config_value = :config_value WHERE lang = '" . NV_LANG_DATA . "' AND module = :module_name AND config_name = :config_name");
    $sth->bindParam(':module_name', $module_name, PDO::PARAM_STR);
    foreach ($data as $config_name => $config_value) {
        $sth->bindParam(':config_name', $config_name, PDO::PARAM_STR);
        $sth->bindParam(':config_value', $config_value, PDO::PARAM_STR);
        $sth->execute();
    }

    nv_insert_logs(NV_LANG_DATA, $module_name, $lang_module['config'], "Config", $admin_info['userid']);
    $nv_Cache->delMod('settings');

    Header("Location: " . NV_BASE_ADMINURL . "index.php?" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . '=' . $op);
    die();
}

$array_config['ck_infoemail'] = $array_config['infoemail'] ? 'checked="checked"' : '';

$xtpl = new XTemplate($op . ".tpl", NV_ROOTDIR . "/themes/" . $global_config['module_theme'] . "/modules/" . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('DATA', $array_config);

$array_config['groups_topic'] = explode(',', $array_config['groups_topic']);
foreach ($groups_list as $group_id => $grtl) {
    if ($group_id != 5 and $group_id != 6) {
        $_groups = array(
            'value' => $group_id,
            'checked' => in_array($group_id, $array_config['groups_topic']) ? ' checked="checked"' : '',
            'title' => $grtl
        );
        $xtpl->assign('GROUP_TOPIC', $_groups);
        $xtpl->parse('main.groups_topic');
    }
}

$array_config['groups_topic_group'] = explode(',', $array_config['groups_topic_group']);
foreach ($groups_list as $group_id => $grtl) {
    if ($group_id != 5 and $group_id != 6) {
        $_groups = array(
            'value' => $group_id,
            'checked' => in_array($group_id, $array_config['groups_topic_group']) ? ' checked="checked"' : '',
            'title' => $grtl
        );
        $xtpl->assign('GROUP_TOPIC_GR', $_groups);
        $xtpl->parse('main.groups_topic_group');
    }
}

$array_config['groups_post'] = explode(',', $array_config['groups_post']);
foreach ($groups_list as $group_id => $grtl) {
    $_groups = array(
        'value' => $group_id,
        'checked' => in_array($group_id, $array_config['groups_post']) ? ' checked="checked"' : '',
        'title' => $grtl
    );
    $xtpl->assign('GROUPPOST', $_groups);
    $xtpl->parse('main.groups_post');
}

$array_editor = array(
    'ckeditor' => 'CKEDITOR',
    'none' => $lang_module['config_editor_none']
);
foreach ($array_editor as $index => $value) {
    $sl = $index == $array_config['editor'] ? 'checked="checked"' : '';
    $xtpl->assign('EDITOR', array(
        'index' => $index,
        'value' => $value,
        'checked' => $sl
    ));
    $xtpl->parse('main.editor');
}

$array_captcha = array(
    0 => $lang_module['config_captcha_0'],
    1 => $lang_module['config_captcha_1'],
    2 => $lang_module['config_captcha_2'],
    3 => $lang_module['config_captcha_3']
);
foreach ($array_captcha as $index => $value) {
    $sl = $index == $array_config['captcha'] ? 'selected="selected"' : '';
    $xtpl->assign('CAPTCHA', array(
        'index' => $index,
        'value' => $value,
        'selected' => $sl
    ));
    $xtpl->parse('main.captcha');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';