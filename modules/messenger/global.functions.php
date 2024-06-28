<?php

/**
 * TMS Content Management System
 * @version 4.x
 * @author Tập Đoàn TMS Holdings <contact@tms.vn>
 * @copyright (C) 2009-2021 Tập Đoàn TMS Holdings. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://tms.vn
 */
if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

$array_config = $module_config[$module_name];

function nv_delete_topic($topicid)
{
    global $db, $module_data, $user_info;

    $topic_info = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic WHERE id=' . $topicid)->fetch();
    if (!empty($topic_info)) {
        if ($topic_info['userid'] == $user_info['userid']) {
            $count = $db->exec('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic WHERE id=' . $topicid);
            if ($count) {
                // xóa bảng trả lời
                $result = $db->query('SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_reply WHERE topicid=' . $topicid);
                while (list ($replyid) = $result->fetch(3)) {
                    nv_delete_reply($replyid, $topicid);
                }

                // xóa bảng người tham gia
                $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_topic_users WHERE topicid=' . $topicid);
            }
            return true;
        }
    }
    return false;
}

/**
 * nv_delete_reply()
 *
 * @param mixed $replyid
 * @param mixed $topicid
 * @return
 *
 */
function nv_delete_reply($replyid, $topicid)
{
    global $db, $db_config, $module_name, $module_data, $array_config, $user_info;

    $reply_info = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_reply WHERE id=' . $replyid)->fetch();
    if ($reply_info) {
        if ($reply_info['userid'] == $user_info['userid']) {
            $count = $db->exec('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_reply WHERE id=' . $replyid);
            if ($count) {
                // Xoa thong bao
                if ($array_config['notifysystem']) {
                    nv_delete_notification(NV_LANG_DATA, $module_name, 'topic_reply', $replyid);
                }

                nv_count_reply($topicid, '-');
                return true;
            }
        }
    }
    return false;
}

/**
 * nv_count_reply()
 *
 * @param mixed $topicid
 * @param string $type
 * @return
 *
 */
function nv_count_reply($topicid, $type = '+')
{
    global $db_config, $db, $module_data;

    $db->query('UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_topic SET reply_count=reply_count ' . $type . ' 1 WHERE id=' . $topicid);
}

function nv_messenger_sendmail($from, $to, $subject, $message)
{
    global $db_config, $db, $module_data, $array_config, $module_file;

    if ($array_config['mailserver'] == 'sendgrid') {
        if (!empty($array_config['sendgrid_apiKey'])) {
            if (file_exists(NV_ROOTDIR . '/modules/' . $module_file . '/sendgrid/vendor/autoload.php')) {
                require (NV_ROOTDIR . '/modules/' . $module_file . '/sendgrid/vendor/autoload.php');

                if (!empty($from)) {
                    if (is_array($from)) {
                        $from = new SendGrid\Email($from[1], $from[0]);
                    } else {
                        $from = new SendGrid\Email(null, $from);
                    }
                } else {
                    return false;
                }

                if (empty($to)) {
                    return false;
                } else {
                    if (is_array($to)) {
                        $to = new SendGrid\Email($to[1], $to[0]);
                    } else {
                        $to = new SendGrid\Email(null, $to);
                    }
                }

                $content = new SendGrid\Content("text/html", $message);
                $mail = new SendGrid\Mail($from, $subject, $to, $content);

                $sg = new \SendGrid($array_config['sendgrid_apiKey']);

                $response = $sg->client->mail()
                    ->send()
                    ->post($mail);
                return $response->statusCode();
            }
        }
    } elseif ($array_config['mailserver'] == 'system') {
        nv_sendmail($from, $to, $subject, $message);
    }
}

function nv_add_mail_queue($tomail, $subject, $message)
{
    global $db, $module_data;

    if (empty($tomail) or nv_check_valid_email($tomail) != '' or empty($subject) or empty($message)) {
        return false;
    }

    try {
        $stmt = $db->prepare('INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_mail_queue(tomail, subject, message) VALUES(:tomail, :subject, :message)');
        $stmt->bindParam(':tomail', $tomail, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        trigger_error($e->getMessage());
    }
}
