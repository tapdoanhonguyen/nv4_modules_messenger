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

$row = array();
$error = array();
$row['id'] = $nv_Request->get_int('id', 'post,get', 0);

$groups_list = nv_groups_list();

if ($row['id'] > 0) {
    $lang_module['addtopic'] = $lang_module['update'];

    $where = ' AND userid=' . $user_info['userid'];

    $row = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic WHERE id=' . $row['id'] . $where)->fetch();
    if (empty($row)) {
        Header('Location: ' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name);
        die();
    }

    $row['list_users'] = $row['list_username'] = array();
    $result = $db->query('SELECT userid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users WHERE topicid=' . $row['id'] . ' AND userid != ' . $row['userid']);
    while (list ($userid) = $result->fetch(3)) {
        $row['list_users'][] = $userid;
        $user = $db->query('SELECT first_name, last_name, username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $userid)->fetch();
        $row['list_username'][$userid] = array(
            'userid' => $userid,
            'username' => nv_show_name_user($user['first_name'], $user['last_name'], $user['username'])
        );
    }
    $row['list_users_old'] = $row['list_users'];
    $row['list_groups_old'] = $row['list_groups'] = !empty($row['list_groups']) ? explode(',', $row['list_groups']) : array();
} else {
    $row['id'] = 0;
    $row['title'] = '';
    $row['content'] = '';
    $row['userid'] = $user_info['userid'];
    $row['user_count'] = 0;
    $row['list_users'] = $row['list_users_old'] = array();
    $row['list_groups'] = array();
}

if ($nv_Request->isset_request('submit', 'post')) {
    $row['title'] = $nv_Request->get_title('title', 'post', '');
    $row['list_users'] = $nv_Request->get_typed_array('list_users', 'post', 'int');
    $row['list_groups'] = $nv_Request->get_typed_array('list_groups', 'post', 'int', array());

    if ($array_config['editor'] == 'none') {
        $row['content'] = $nv_Request->get_textarea('content', '', NV_ALLOWED_HTML_TAGS);
    } else {
        $row['content'] = $nv_Request->get_editor('content', '', NV_ALLOWED_HTML_TAGS);
    }

    if (empty($row['title'])) {
        die(reply_result(array(
            'status' => 'error',
            'input' => 'title',
            'mess' => $lang_module['error_required_title']
        )));
    }

    if (empty($row['content'])) {
        die(reply_result(array(
            'status' => 'error',
            'input' => 'content',
            'mess' => $lang_module['error_required_content']
        )));
    }

    if (empty($row['list_users']) and empty($row['list_groups'])) {
        die(reply_result(array(
            'status' => 'error',
            'input' => 'content',
            'mess' => $lang_module['error_required_list_users']
        )));
    }

    if (nv_use_captcha()) {
        if (!nv_capcha_txt($nv_Request->get_title('fcode', 'post', ''))) {
            die(json_encode(array(
                'status' => 'error',
                'input' => 'fcode',
                'mess' => $lang_module['error_captcha']
            )));
        }
    }

    try {
        $list_users = sizeof($row['list_users']) + 1;
        $list_groups = !empty($row['list_groups']) ? implode(',', $row['list_groups']) : '';
        if (empty($row['id'])) {
            $_sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_topic (title, content, userid, addtime, reply_time, user_count, list_groups) VALUES (:title, :content, :userid, ' . NV_CURRENTTIME . ', ' . NV_CURRENTTIME . ', :user_count, :list_groups)';
            $data_insert = array();
            $data_insert['title'] = $row['title'];
            $data_insert['content'] = $row['content'];
            $data_insert['userid'] = $row['userid'];
            $data_insert['user_count'] = $list_users;
            $data_insert['list_groups'] = $list_groups;
            $new_id = $db->insert_id($_sql, 'id', $data_insert);
        } else {
            $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_topic SET title = :title, content = :content, edittime = :edittime, user_count = :user_count, list_groups = :list_groups WHERE id=' . $row['id']);
            $stmt->bindParam(':title', $row['title'], PDO::PARAM_STR);
            $stmt->bindParam(':content', $row['content'], PDO::PARAM_STR, strlen($row['content']));
            $stmt->bindParam(':edittime', $row['edittime'], PDO::PARAM_INT);
            $stmt->bindParam(':user_count', $list_users, PDO::PARAM_INT);
            $stmt->bindParam(':list_groups', $list_groups, PDO::PARAM_STR);
            if ($exc = $stmt->execute()) {
                $new_id = $row['id'];
            }
        }

        if ($new_id > 0) {

            $array_send_mail_userid = array();

            // cập nhật bảng người tham gia
            if (empty($row['id'])) {
                $row['list_users'][] = $row['userid'];
            }

            if ($row['list_users'] != $row['list_users_old']) {
                $sth = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users (topicid, userid) VALUES(:topicid, :userid)');
                foreach ($row['list_users'] as $userid) {
                    if (!in_array($userid, $row['list_users_old'])) {
                        $array_send_mail_userid[] = $userid;
                        $sth->bindParam(':topicid', $new_id, PDO::PARAM_INT);
                        $sth->bindParam(':userid', $userid, PDO::PARAM_INT);
                        $sth->execute();
                    }
                }

                foreach ($row['list_users_old'] as $userid) {
                    if (!in_array($userid, $row['list_users'])) {
                        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users WHERE userid = ' . $userid . ' AND topicid=' . $new_id);
                    }
                }
            }

            // lấy danh sách user id từ nhóm
            $array_config['groups_topic_group'] = !empty($array_config['groups_topic_group']) ? array_map('intval', explode(',', $array_config['groups_topic_group'])) : array();
            $check = array_intersect($array_config['groups_topic_group'], $user_info['in_groups']);
            if (!empty($check) and $row['list_groups'] != $row['list_groups_old']) {
                $sth = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users (topicid, userid, group_id) VALUES(:topicid, :userid, :group_id)');
                foreach ($row['list_groups'] as $group_id) {
                    if (!in_array($group_id, $row['list_groups_old'])) {
                        $result = $db->query('SELECT userid, group_id FROM ' . NV_GROUPS_GLOBALTABLE . '_users WHERE group_id=' . $group_id);
                        while (list ($userid, $_group_id) = $result->fetch(3)) {
                            $array_send_mail_userid[] = $userid;
                            $sth->bindParam(':topicid', $new_id, PDO::PARAM_INT);
                            $sth->bindParam(':userid', $userid, PDO::PARAM_INT);
                            $sth->bindParam(':group_id', $_group_id, PDO::PARAM_INT);
                            $sth->execute();
                        }
                    }
                }

                foreach ($row['list_groups_old'] as $group_id) {
                    if (!in_array($group_id, $row['list_groups'])) {
                        $result = $db->query('SELECT userid, group_id FROM ' . NV_GROUPS_GLOBALTABLE . '_users WHERE group_id=' . $group_id);
                        while (list ($userid, $_group_id) = $result->fetch(3)) {
                            $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users WHERE userid = ' . $userid . ' AND topicid=' . $new_id . ' AND group_id=' . $_group_id);
                        }
                    }
                }
            }

            //  thêm vào hàng đợi gửi mail
            if ($array_config['infoemail'] and !empty($array_send_mail_userid)) {
                $array_send_mail_userid = array_unique($array_send_mail_userid);
                $array_user_info = array();
                $result = $db->query('SELECT userid, email FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid IN (' . implode(',', $array_send_mail_userid) . ')');
                while (list ($userid, $email) = $result->fetch(3)) {
                    $array_user_info[$userid] = $email;
                }

                $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_econtent';
                $content = $nv_Cache->db($sql, 'action', $module_name);
                $content = nv_unhtmlspecialchars($content['topic_content']['econtent']);

                $array_replace = array(
                    'TOPIC_USER' => $user_info['full_name'],
                    'TOPIC_TITLE' => $row['title'],
                    'TOPIC_CONTENT' => $row['content'],
                    'TOPIC_URL' => NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . change_alias($row['title']) . '-' . $new_id, true),
                    'SITE_NAME' => $global_config['site_name'],
                    'SITE_URL' => $global_config['site_url']
                );
                foreach ($array_replace as $index => $value) {
                    $content = str_replace('[' . $index . ']', $value, $content);
                }

                $subject = $global_config['site_name'] . ' - ' . $row['title'];
                foreach ($array_user_info as $userid => $email) {
                    if ($userid != $user_info['userid']) {
                        nv_add_mail_queue($email, $subject, $content);
                    }
                }
            }

            die(reply_result(array(
                'status' => 'success',
                'redirect' => nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . change_alias($row['title']) . '-' . $new_id, true)
            )));
        }
    } catch (PDOException $e) {
        trigger_error($e->getMessage());
        die(reply_result(array(
            'status' => 'error',
            'mess' => $lang_module['empty_database']
        )));
    }
    die();
}

if ($array_config['editor'] == 'ckeditor') {
    if (defined('NV_EDITOR')) {
        require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
    } elseif (!nv_function_exists('nv_aleditor') and file_exists(NV_ROOTDIR . '/' . NV_EDITORSDIR . '/ckeditor/ckeditor.js')) {
        define('NV_EDITOR', true);
        define('NV_IS_CKEDITOR', true);
        $my_head .= '<script type="text/javascript" src="' . NV_BASE_SITEURL . NV_EDITORSDIR . '/ckeditor/ckeditor.js"></script>';

        function nv_aleditor($textareaname, $width = '100%', $height = '250px', $val = '', $customtoolbar = '')
        {
            global $module_data;
            $return = '<textarea style="width: ' . $width . '; height:' . $height . ';" id="' . $module_data . '_' . $textareaname . '" name="' . $textareaname . '">' . $val . '</textarea>';
            $return .= "<script type=\"text/javascript\">
		CKEDITOR.replace( '" . $module_data . "_" . $textareaname . "', {" . (!empty($customtoolbar) ? 'toolbar : "' . $customtoolbar . '",' : '') . " width: '" . $width . "',height: '" . $height . "',});
		</script>";
            return $return;
        }
    }

    $row['content'] = htmlspecialchars(nv_editor_br2nl($row['content']));
    if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
        $row['content'] = nv_aleditor('content', '100%', '250px', $row['content'], 'Basic');
    } else {
        $row['content'] = '<textarea style="width:100%;height:250px" name="content">' . $row['content'] . '</textarea>';
    }
} else {
    $row['content'] = '<textarea style="width:100%;height:250px" name="content" id="content">' . $row['content'] . '</textarea>';
}

$contents = nv_theme_messenger_content($row, $error);

$page_title = $lang_module['addtopic'];
$array_mod_title[] = array(
    'title' => $page_title
);

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';