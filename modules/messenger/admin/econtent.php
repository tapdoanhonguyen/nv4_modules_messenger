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

$row = array();

if (defined('NV_EDITOR')) {
    require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
}

if ($nv_Request->isset_request('submit', 'post')) {
    $row['topic_content'] = $nv_Request->get_editor('econtent_topic_content', '', NV_ALLOWED_HTML_TAGS);
    $row['reply_content'] = $nv_Request->get_editor('econtent_reply_content', '', NV_ALLOWED_HTML_TAGS);

    $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_econtent SET econtent = :econtent WHERE action=:action');
    foreach ($row as $config_name => $config_value) {
        $stmt->bindParam(':econtent', $config_value, PDO::PARAM_STR);
        $stmt->bindParam(':action', $config_name, PDO::PARAM_STR);
        $exc = $stmt->execute();
    }

    $nv_Cache->delMod($module_name);
    Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
    die();
} else {
    $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_econtent');
    while ($_row = $result->fetch()) {
        $_row['econtent'] = htmlspecialchars(nv_editor_br2nl($_row['econtent']));
        if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
            $_row['econtent'] = nv_aleditor('econtent_' . $_row['action'], '100%', '300px', $_row['econtent']);
        } else {
            $_row['econtent'] = '<textarea style="width:100%;height:300px" name="econtent_' . $_row['action'] . '">' . $_row['econtent'] . '</textarea>';
        }
        $row[] = $_row;
    }
}

$array_note = array(
    'topic_content' => array(
        'TOPIC_USER' => $lang_module['econtent_topic_user'],
        'TOPIC_TITLE' => $lang_module['econtent_topic_title'],
        'TOPIC_CONTENT' => $lang_module['econtent_topic_content'],
        'TOPIC_URL' => $lang_module['econtent_topic_url'],
        'SITE_NAME' => $lang_module['econtent_site_name'],
        'SITE_URL' => $lang_module['econtent_site_url']
    ),
    'reply_content' => array(
        'TOPIC_USER' => $lang_module['econtent_topic_user'],
        'TOPIC_TITLE' => $lang_module['econtent_topic_title'],
        'TOPIC_CONTENT' => $lang_module['econtent_topic_content'],
        'TOPIC_URL' => $lang_module['econtent_topic_url'],
        'REPLY_USER' => $lang_module['econtent_reply_user'],
        'REPLY_CONTENT' => $lang_module['econtent_reply_content'],
        'SITE_NAME' => $lang_module['econtent_site_name'],
        'SITE_URL' => $lang_module['econtent_site_url']
    )
);

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);

if (!empty($row)) {
    foreach ($row as $value) {
        $value['title'] = $lang_module['econtent_' . $value['action']];
        $xtpl->assign('ROW', $value);
        $xtpl->parse('main.title');

        foreach ($array_note[$value['action']] as $index => $value) {
            $xtpl->assign('NOTE', array(
                'index' => $index,
                'value' => $value
            ));
            $xtpl->parse('main.content.note');
        }

        $xtpl->parse('main.content');
    }
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['econtent'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';