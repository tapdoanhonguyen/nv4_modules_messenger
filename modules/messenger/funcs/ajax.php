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

if ($nv_Request->isset_request('get_user_json', 'post, get')) {
    $q = $nv_Request->get_title('q', 'post, get', '');
    $topicid = $nv_Request->get_int('topicid', 'post, get', 0);

    $db->sqlreset()
        ->select('userid, username, email, first_name, last_name')
        ->from(NV_USERS_GLOBALTABLE)
        ->where('(username LIKE :username OR email LIKE :email OR first_name like :first_name OR last_name like :last_name) AND userid != ' . $user_info['userid'] .' AND userid NOT IN (SELECT userid FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users WHERE topicid=' . $topicid . ')')
        ->order('username ASC')
        ->limit(20);

    $sth = $db->prepare($db->sql());
    $sth->bindValue(':username', '%' . $q . '%', PDO::PARAM_STR);
    $sth->bindValue(':email', '%' . $q . '%', PDO::PARAM_STR);
    $sth->bindValue(':first_name', '%' . $q . '%', PDO::PARAM_STR);
    $sth->bindValue(':last_name', '%' . $q . '%', PDO::PARAM_STR);
    $sth->execute();

    $array_data = array();
    while (list ($userid, $username, $email, $first_name, $last_name) = $sth->fetch(3)) {
        $array_data[] = array(
            'id' => $userid,
            'username' => $username,
            'fullname' => nv_show_name_user($first_name, $last_name)
        );
    }

    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/json');

    ob_start('ob_gzhandler');
    echo json_encode($array_data);
    exit();
}

if ($nv_Request->isset_request('delete_list', 'post')) {
    $listall = $nv_Request->get_title('listall', 'post', '');
    $array_id = explode(',', $listall);

    if (!empty($array_id)) {
        foreach ($array_id as $id) {
            nv_delete_topic($id);
        }
        $nv_Cache->delMod($module_name);
        die('OK');
    }
    die('NO');
}