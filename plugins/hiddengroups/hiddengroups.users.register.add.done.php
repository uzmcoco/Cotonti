<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=users.adduser.done
[END_COT_EXT]
==================== */

/**
 * Hidden groups
 *
 * @package HiddenGroups
 * @copyright (c) Cotonti Team
 * @license https://github.com/Cotonti/Cotonti/blob/master/License.txt
 */

defined('COT_CODE') or die('Wrong URL.');

if (!empty(cot::$cache) && !empty(cot::$cache->db)) {
    cot::$cache->db->remove('cot_hiddenusers', 'system');
}