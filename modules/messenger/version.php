<?php
/**
 * TMS Content Management System
 * @version 4.x
 * @author Tập Đoàn TMS Holdings <contact@tms.vn>
 * @copyright (C) 2009-2021 Tập Đoàn TMS Holdings. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://tms.vn
 */
if (! defined('NV_MAINFILE'))
    die('Stop!!!');

$module_version = array(
    'name' => 'Messenger',
    'modfuncs' => 'main,viewtopic,content,ajax,cronjobs',
    'change_alias' => 'main,content',
    'submenu' => 'main,content',
    'is_sysmod' => 0,
    'virtual' => 1,
    'version' => '4.5.00',
    'date' => 'Saturday, July 17, 2021 4:00:00 PM GMT+07:00',
    'author' => 'Tập Đoàn TMS Holdings <contact@tms.vn>',
    'uploads_dir' => array(
        $module_name
    ),
    'note' => ''
);