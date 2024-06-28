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

if ($nv_Request->isset_request('reply', 'post,get')) {
    $array_data = array();
    $array_data['replyid'] = $nv_Request->get_int('replyid', 'post', 0);
    $array_data['topicid'] = $nv_Request->get_int('topicid', 'post', 0);

    if ($array_config['editor'] == 'none') {
        $array_data['content'] = $nv_Request->get_textarea('content', '', NV_ALLOWED_HTML_TAGS);
    } else {
        $array_data['content'] = $nv_Request->get_editor('content', '', NV_ALLOWED_HTML_TAGS);
    }

    $topic_info = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic WHERE id=' . $array_data['topicid'])->fetch();
    if (!$topic_info) {
        die(reply_result(array(
            'status' => 'error',
            'mess' => $lang_module['error_unknow']
        )));
    }

    if (empty($array_data['topicid'])) {
        die(reply_result(array(
            'status' => 'error',
            'mess' => $lang_module['empty_topicid']
        )));
    }

    if (empty($array_data['content'])) {
        die(reply_result(array(
            'status' => 'error',
            'mess' => $lang_module['empty_content']
        )));
    }

    if (empty($array_data['replyid']) and nv_use_captcha()) {
        if (!nv_capcha_txt($nv_Request->get_title('fcode', 'post', ''))) {
            die(json_encode(array(
                'status' => 'error',
                'input' => 'fcode',
                'mess' => $lang_module['error_captcha']
            )));
        }
    }

    $userid = $user_info['userid'];

    $new_id = 0;
    if (empty($array_data['replyid'])) {
        $_sql = 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_reply (topicid, content, userid, addtime) VALUES (:topicid, :content, ' . $userid . ', ' . NV_CURRENTTIME . ')';

        $data_insert = array();
        $data_insert['topicid'] = $array_data['topicid'];
        $data_insert['content'] = $array_data['content'];
        $new_id = $db->insert_id($_sql, 'id', $data_insert);
    } else {
        $stmt = $db->prepare('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_reply SET content = :content, edittime = ' . NV_CURRENTTIME . ' WHERE id=' . $array_data['replyid']);
        $stmt->bindParam(':content', $array_data['content'], PDO::PARAM_STR, strlen($array_data['content']));
        if ($stmt->execute()) {
            $new_id = $array_data['replyid'];
        }
    }

    if ($new_id > 0) {
        $array_data['userid'] = $userid;
        $array_data['avata'] = NV_BASE_SITEURL . 'themes/' . $module_info['template'] . '/images/users/no_avatar.png';

        if ($userid > 0) {
            list ($username, $first_name, $last_name, $photo) = $db->query('SELECT username, first_name, last_name, photo FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $userid)->fetch(3);
            $array_data['poster'] = nv_show_name_user($first_name, $last_name, $username);
            if (file_exists(NV_ROOTDIR . '/' . $photo) and !empty($photo)) {
                $array_data['avata'] = NV_BASE_SITEURL . $photo;
            }
        } else {
            $array_data['poster'] = $lang_module['guest'];
        }

        $array_data['addtime'] = nv_date('H:i d/m/Y', NV_CURRENTTIME);

        nv_count_reply($array_data['topicid']);

        // cập nhật thời gian trả lời cuối cùng
        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_topic SET reply_time=' . NV_CURRENTTIME . ' WHERE id=' . $array_data['topicid']);

        // đánh dấu trạng thái chưa xem trả lời mới nhất
        $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users SET viewed=0 WHERE topicid=' . $array_data['topicid'] . ' AND userid != ' . $user_info['userid']);

        //  thêm vào hàng đợi gửi mail
        if ($array_config['infoemail'] and empty($array_data['replyid'])) {
            $array_user_info = nv_get_topic_users($array_data['topicid']);
            if (!empty($array_user_info)) {
                $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_econtent';
                $content = $nv_Cache->db($sql, 'action', $module_name);
                $content = nv_unhtmlspecialchars($content['reply_content']['econtent']);

                $array_replace = array(
                    'TOPIC_USER' => $array_user_info[$topic_info['userid']]['fullname'],
                    'TOPIC_TITLE' => $topic_info['title'],
                    'TOPIC_CONTENT' => $topic_info['content'],
                    'TOPIC_URL' => NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . change_alias($topic_info['title']) . '-' . $topic_info['id'], true),
                    'REPLY_USER' => $user_info['fullname'],
                    'REPLY_CONTENT' => $array_data['content'],
                    'SITE_NAME' => $global_config['site_name'],
                    'SITE_URL' => $global_config['site_url']
                );
                foreach ($array_replace as $index => $value) {
                    $content = str_replace('[' . $index . ']', $value, $content);
                }

                $subject = $global_config['site_name'] . ' - ' . $topic_info['title'];
                foreach ($array_user_info as $userid => $_user_info) {
                    if ($userid != $user_info['userid']) {
                        nv_add_mail_queue($email, $subject, $content);
                    }
                }
            }
        }

        if (empty($array_data['replyid'])) {
            $array_userid = array();
            $topic_url = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . change_alias($topic_info['title']) . '-' . $array_data['topicid'], true) . '#p' . $new_id;
            die(reply_result(array(
                'status' => 'success',
                'notifyid' => $notify_id,
                'url' => $topic_url,
                'title' => $topic_info['title'],
                'poster_id' => $array_data['userid'],
                'poster_name' => $array_data['poster'],
                'addtime' => nv_date('H:i d/m/Y', NV_CURRENTTIME),
                'feedback' => $array_userid,
                'data' => nv_theme_messenger_reply_item($array_data)
            )));
        }
    }
    die(reply_result(array(
        'status' => 'error',
        'mess' => $lang_module['empty_database']
    )));
}

if ($nv_Request->isset_request('delete_reply', 'post')) {
    $replyid = $nv_Request->get_int('replyid', 'post', 0);
    $topicid = $nv_Request->get_int('topicid', 'post', 0);
    $is_topic = $nv_Request->get_int('is_topic', 'post', 0);

    if (empty($replyid) or empty($topicid)) die('NO_' . $lang_module['empty_replyid']);

    if (!$is_topic) {
        if (nv_delete_reply($replyid, $topicid)) {
            die('OK');
        }
    } else {
        if (nv_delete_topic($topicid)) {
            die('OK');
        }
    }
    die('NO_' . $lang_module['empty_database']);
}

$groups_list = nv_groups_list();
$per_page = $array_config['per_topic'];
$reply_content['id'] = $nv_Request->get_int('id', 'get', 0);
if ($reply_content['id'] > 0) {
    //
} else {
    $reply_content['topicid'] = 0;
    $reply_content['content'] = '';
}

$topic_info = $db->query('SELECT t1.*, t2.viewed FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic t1 INNER JOIN ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users t2 ON t1.id=t2.topicid WHERE id=' . $topicid . ' AND t2.userid=' . $user_info['userid'])->fetch();
if (empty($topic_info)) {
    $redirect = '<meta http-equiv="Refresh" content="3;URL=' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name, true) . '" />';
    nv_info_die($lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content'] . $redirect, 404);
}

$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . change_alias($topic_info['title']) . '-' . $topic_info['id'];
$array_topic_users = nv_get_topic_users($topic_info['id']);

$topic_info['topicid'] = $topic_info['id'];
$topic_info['addtime'] = nv_date('H:i d/m/Y', $topic_info['addtime']);
$topic_info['reply_time'] = nv_date('H:i d/m/Y', $topic_info['reply_time']);
$photo = '';
if ($topic_info['userid'] > 0) {
    list ($username, $first_name, $last_name, $photo) = $db->query('SELECT username, first_name, last_name, photo FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $topic_info['userid'])->fetch(3);
    $topic_info['poster'] = nv_show_name_user($first_name, $last_name, $username);
} else {
    $topic_info['poster'] = $lang_module['guest'];
}

if (file_exists(NV_ROOTDIR . '/' . $photo) and !empty($photo)) {
    $topic_info['avata'] = NV_BASE_SITEURL . $photo;
} else {
    $topic_info['avata'] = NV_BASE_SITEURL . 'themes/' . $module_info['template'] . '/images/users/no_avatar.png';
}

if (!$topic_info['viewed']) {
    $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users SET viewed=1 WHERE topicid=' . $topicid . ' AND userid=' . $user_info['userid']);
}

if ($array_config['editor'] == 'ckeditor') {
    if (defined('NV_EDITOR')) {
        require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
    } elseif (!nv_function_exists('nv_aleditor') and file_exists(NV_ROOTDIR . '/' . NV_EDITORSDIR . '/ckeditor/ckeditor.js')) {
        define('NV_EDITOR', true);
        define('NV_IS_CKEDITOR', true);
        $my_head .= '<script type="text/javascript" src="' . NV_BASE_SITEURL . NV_EDITORSDIR . '/ckeditor/ckeditor.js"></script>';

        function nv_aleditor($textareaname, $width = '100%', $height = '450px', $val = '', $customtoolbar = '')
        {
            global $module_data;
            $return = '<textarea style="width: ' . $width . '; height:' . $height . ';" id="' . $module_data . '_' . $textareaname . '" name="' . $textareaname . '">' . $val . '</textarea>';
            $return .= "<script type=\"text/javascript\">
		CKEDITOR.replace( '" . $module_data . "_" . $textareaname . "', {" . (!empty($customtoolbar) ? 'toolbar : "' . $customtoolbar . '",' : '') . " width: '" . $width . "',height: '" . $height . "',});
		</script>";
            return $return;
        }
    }
    $reply_content['content'] = htmlspecialchars(nv_editor_br2nl($reply_content['content']));
    if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor')) {
        $reply_content['content'] = nv_aleditor('content', '100%', '200px', $reply_content['content'], 'Basic');
    } else {
        $reply_content['content'] = '<textarea class="form-control" style="width:100%;height:200px" name="content">' . $reply_content['content'] . '</textarea>';
    }
} else {
    $reply_content['content'] = '<textarea class="form-control" style="width:100%;height:300px" name="content" id="content">' . $reply_content['content'] . '</textarea>';
}

$array_reply = array();
$db->sqlreset()
    ->select('COUNT(*)')
    ->from(NV_PREFIXLANG . '_' . $module_data . '_reply')
    ->where('topicid=' . $topicid);

$all_page = $db->query($db->sql())
    ->fetchColumn();

$db->select('*')
    ->order('id')
    ->limit($per_page)
    ->offset(($page - 1) * $per_page);

$_query = $db->query($db->sql());
while ($_row = $_query->fetch()) {
    list ($username, $first_name, $last_name, $photo) = $db->query('SELECT username, first_name, last_name, photo FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $_row['userid'])->fetch(3);
    $_row['poster'] = nv_show_name_user($first_name, $last_name, $username);

    if (file_exists(NV_ROOTDIR . '/' . $photo) and !empty($photo)) {
        $_row['avata'] = NV_BASE_SITEURL . $photo;
    } else {
        $_row['avata'] = NV_BASE_SITEURL . 'themes/' . $module_info['template'] . '/images/users/no_avatar.png';
    }
    $_row['addtime'] = nv_date('H:i d/m/Y', $_row['addtime']);
    $array_reply[] = $_row;
}

$time_set = $nv_Request->get_int($module_data . '_' . $op . '_' . $topic_info['id'], 'session');
if (empty($time_set)) {
    $nv_Request->set_Session($module_data . '_' . $op . '_' . $topic_info['id'], NV_CURRENTTIME);
    $query = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_topic SET view_count=view_count+1 WHERE id=' . $topic_info['id'];
    $db->query($query);
}

$page_title = $topic_info['title'];

$array_mod_title[] = array(
    'title' => $page_title,
    'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . change_alias($topic_info['title']) . '-' . $topic_info['id']
);

if ($page > 1) {
    $page_title = $page_title . ' - ' . $lang_global['page'] . ' ' . $page;
}
$page = nv_alias_page($page_title, $base_url, $all_page, $per_page, $page);

$contents = nv_theme_messenger_viewtopic($topic_info, $array_reply, $reply_content, $array_topic_users, $page);

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';