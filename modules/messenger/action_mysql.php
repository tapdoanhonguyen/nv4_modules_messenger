<?php
/**
 * TMS Content Management System
 * @version 4.x
 * @author Tập Đoàn TMS Holdings <contact@tms.vn>
 * @copyright (C) 2009-2021 Tập Đoàn TMS Holdings. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://tms.vn
 */
if (!defined('NV_IS_FILE_MODULES')) die('Stop!!!');

$sql_drop_module = array();
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_topic";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_topic_users";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_reply";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_econtent";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_mail_queue";

$sql_create_module = $sql_drop_module;
$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_topic(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  content text NOT NULL,
  userid int(11) unsigned NOT NULL,
  addtime int(11) unsigned NOT NULL,
  edittime INT(11) UNSIGNED NOT NULL DEFAULT '0',
  reply_time INT(11) UNSIGNED NOT NULL DEFAULT '0',
  reply_count mediumint(8) unsigned NOT NULL DEFAULT '0',
  view_count int(11) unsigned NOT NULL DEFAULT '0',
  user_count smallint(4) unsigned NOT NULL,
  list_groups varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_topic_users(
  topicid int(11) NOT NULL AUTO_INCREMENT,
  userid mediumint(8) unsigned NOT NULL,
  follow tinyint(1) unsigned NOT NULL DEFAULT '1',
  group_id smallint(5) unsigned NOT NULL DEFAULT '0',
  viewed tinyint(1) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY topicid(topicid,userid,group_id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_reply(
  id int(11) NOT NULL AUTO_INCREMENT,
  topicid mediumint(8) unsigned NOT NULL,
  content text NOT NULL,
  userid int(11) unsigned NOT NULL,
  addtime int(11) unsigned NOT NULL,
  edittime INT(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_econtent(
  action varchar(100) NOT NULL,
  econtent text NOT NULL,
  PRIMARY KEY (action)
) ENGINE=MyISAM";
$sql_create_module[] = "INSERT INTO " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_econtent (action, econtent) VALUES('topic_content', '<strong>&#91;TOPIC_USER&#93;</strong> đã khởi tạo hội thoại <b>&#91;TOPIC_TITLE&#93;&nbsp;</b>có sự tham gia của bạn, dưới đây là nội dung: <blockquote>&#91;TOPIC_CONTENT&#93;</blockquote> <a href=\"&#91;TOPIC_URL&#93;\">Xem hội thoại</a><br /> ----------------------------------------------------------------------------------------<br /> Đây là thông báo tự động được gửi từ <b>&#91;SITE_NAME&#93;</b>. Vui lòng không trả lời thư này!')";
$sql_create_module[] = "INSERT INTO " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_econtent (action, econtent) VALUES('reply_content', '<strong>&#91;TOPIC_USER&#93;</strong> đã trả lời trong hội thoại <b>&#91;TOPIC_TITLE&#93;</b>, dưới đây là nội dung: <blockquote>&#91;REPLY_CONTENT&#93;</blockquote> <a href=\"&#91;TOPIC_URL&#93;\">Xem hội thoại</a><br /> ----------------------------------------------------------------------------------------<br /> Đây là thông báo tự động được gửi từ <b>&#91;SITE_NAME&#93;</b>. Vui lòng không trả lời thư này!')";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_mail_queue(
  id smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  tomail varchar(100) NOT NULL,
  subject varchar(255) NOT NULL,
  message text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$data = array(
    'per_page' => 15,
    'per_topic' => 15,
    'groups_post' => 4,
    'groups_topic' => 6,
    'groups_topic_group' => '1,2,3',
    'editor' => 'ckeditor',
    'captcha' => 0,
    'infoemail' => 1
);

foreach ($data as $config_name => $config_value) {
    $sql_create_module[] = "INSERT INTO " . NV_CONFIG_GLOBALTABLE . " (lang, module, config_name, config_value) VALUES ('" . $lang . "', " . $db->quote($module_name) . ", " . $db->quote($config_name) . ", " . $db->quote($config_value) . ")";
}