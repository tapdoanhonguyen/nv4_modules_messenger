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

// gửi mail trong hàng đợi
$result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mail_queue');
while ($row = $result->fetch()) {
    $from = array(
        $global_config['site_name'],
        $global_config['site_email']
    );

    if (nv_sendmail($from, $row['tomail'], $row['subject'], $row['message'])) {
        $db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_mail_queue WHERE id=' . $row['id']);
    }
}
