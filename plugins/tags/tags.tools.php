<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=tools
[END_COT_EXT]
==================== */
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

list(cot::$usr['auth_read'], cot::$usr['auth_write'], cot::$usr['isadmin']) = cot_auth('plug', 'tags');
cot_block(cot::$usr['isadmin']);

require_once cot_incfile('tags', 'plug');

$tt = new XTemplate(cot_tplfile('tags.tools', 'plug', true));

$adminhelp = cot::$L['info_desc'];
$adminTitle = cot::$L['tags_All'];

$cfg['maxrowsperpage'] = 30;

$action = cot_import('action', 'P', 'TXT');
$tag = cot_import('tag', 'R', 'TXT');
$tag = !empty($tag) ? str_replace('_', ' ', $tag) : '';
list($pg, $d, $durl) = cot_import_pagenav('d', $cfg['maxrowsperpage']);

$sorttype = cot_import('sorttype', 'R', 'ALP');
$sorttype = empty($sorttype) ? 'tag' : $sorttype;
$sort_type = array(
	'tag' => cot::$L['Code'],
	'tag_cnt' => cot::$L['Count'],
	'length' => cot::$L['tags_length']
);
if ($sorttype == 'tag') {
	$admin_tags_join_sorttype = "t.tag";
} elseif ($sorttype == 'length') {
	$admin_tags_join_sorttype = "length(t.tag)";
} else {
	$admin_tags_join_sorttype = $sorttype;
}

$sortway = cot_import('sortway', 'R', 'ALP');
$sortway = empty($sortway) ? 'asc' : $sortway;
$sort_way = array(
	'asc' => cot::$L['Ascending'],
	'desc' => cot::$L['Descending']
);

$filter = cot_import('filter', 'R', 'TXT');
$filter = empty($filter) ? 'all' : $filter;
$filter_type = array(
	'all' => cot::$L['All'],
);
foreach(range(chr(0xC0), chr(0xDF)) as $i) {
	$i = iconv('CP1251', 'UTF-8', $i);
	$filter_type[$i] = $i;
}
if ($filter == 'all') {
	$admin_tags_join_where = "";
} else {
	$admin_tags_join_where = "AND t.tag LIKE '".$filter."%'";
}

$admin_tags_join_fields = '';
$admin_tags_join_tables = '';

/* === Hook  === */
foreach (cot_getextplugins('admin.tags.first') as $pl) {
	include $pl;
}
/* ===== */

$adminwarnings = '';

if ($action == cot::$L['Delete']) {
	cot_check_xp();
	foreach (cot_getextplugins('admin.tags.delete') as $pl)
	{
		include $pl;
	}
	/* ===== */
	$db->delete($db_tags, "tag='".htmlspecialchars($tag)."'");
	$sql = $db->delete($db_tag_references, "tag='".htmlspecialchars($tag)."'");
	$adminwarnings = ($sql) ? cot_message('adm_tag_already_del') : $L['Error'];
} elseif ($action == $L['Edit']) {
	cot_check_xp();
	$old_tag = str_replace('_', ' ', cot_import('old_tag', 'R', 'TXT'));

	foreach (cot_getextplugins('admin.tags.edit') as $pl)
	{
		include $pl;
	}
	/* ===== */

	if (cot_tag_exists($tag)) {
		cot_message('adm_tag_already exists', 'warning');
	} else {
		$db->update($db_tags, array("tag" => htmlspecialchars($tag)), "tag='".$db->prep($old_tag)."'");
		$db->update($db_tag_references, array("tag" => htmlspecialchars($tag)), "tag='".$db->prep($old_tag)."'");
		$adminwarnings = ($sql) ? cot_message('adm_tag_already_edit') : $L['Error'];
	}
} elseif (!empty($tag)) {
	$admin_tags_join_where = "AND t.tag LIKE '".$tag."'";
}

$is_adminwarnings = isset($adminwarnings);

if (cot_module_active('page')) {
	require_once cot_incfile('page', 'module');
}
if (cot_module_active('forums')) {
	require_once cot_incfile('forums', 'module');
}

$totalitems = cot::$db->query("SELECT distinct(tag) FROM " . cot::$db->tag_references . " AS t WHERE 1 " .
    $admin_tags_join_where)->rowCount();
$pagenav = cot_pagenav(
    'admin',
    'm=other&p=tags&sorttype=' . $sorttype . '&sortway=' . $sortway . '&filter=' . $filter,
    $d,
    $totalitems,
    cot::$cfg['maxrowsperpage'],
    'd',
    '',
    cot::$cfg['jquery'] && $cfg['turnajax']
);

$sql = "SELECT t.tag, COUNT(*) AS tag_cnt, GROUP_CONCAT(t.tag_area,':',t.tag_item SEPARATOR ',') AS tag_grp $admin_tags_join_fields
	FROM " . cot::$db->tag_references . " AS t 
	$admin_tags_join_tables
	WHERE 1 $admin_tags_join_where
	GROUP BY t.tag
	ORDER BY $admin_tags_join_sorttype $sortway LIMIT $d, " . $cfg['maxrowsperpage'];

$tags = cot::$db->query($sql)->fetchAll();

$ii = 0;
/* === Hook - Part1 : Set === */
$extp = cot_getextplugins('admin.tags.loop');
/* ===== */
foreach ($tags  as $row) {
    if (isset($cot_extrafields[$db_tag_references])) {
        foreach ($cot_extrafields[$db_tag_references] as $exfld) {
            $tag = mb_strtoupper($exfld['field_name']);
            $tt->assign(array(
                'ADMIN_TAGS_' . $tag . '_TITLE' => isset($L['tags_' . $exfld['field_name'] . '_title']) ? $L['tags_' . $exfld['field_name'] . '_title'] : $exfld['field_description'],
                'ADMIN_TAGS_' . $tag => cot_build_extrafields_data('tags', $exfld, $row['tag_'.$exfld['field_name']]),
                'ADMIN_TAGS_' . $tag . '_VALUE' => $row['tag_'.$exfld['field_name']],
            ));
        }
    }
    $tagArea = [];
    if (!empty($row['tag_grp'])) {
        $item_mas = array();
        $items = explode(',', $row['tag_grp']);
        foreach ($items as $val) {
            $item = explode(':', $val);
            $item_mas[$item[0]][] = $item[1];
        }
        foreach ($item_mas as $k => $v) {
            $area = $k;
            if (!empty(cot::$L[$k])) {
                $area = cot::$L[$k];
            } else {
                $tmp = mb_strtoupper(mb_substr($k, 0, 1));
                $tmp .= mb_substr($k, 1);
                if (!empty(cot::$L[$tmp])) {
                    $area = cot::$L[$tmp];
                }
            }

            if (!in_array($k, $tagArea)) {
                $tagArea[] = $area;
            }
            // Todo load all pages with one query
            if ($k == 'pages') {
                foreach ($v as $kk => $vv) {
                    $row_item = cot_generate_pagetags($vv, 'ADMIN_TAGS_ITEM_', 200);
                    if (!empty($row_item)) {
                        $tt->assign($row_item);
                        //$tt->assign(cot_generate_usertags($row_item['page_ownerid'], 'ADMIN_TAGS_PAGE_OWNER_'), htmlspecialchars($row['user_name']));
                    } else {
                        $row_item['ADMIN_TAGS_ITEM_ID'] = $vv;
                        $row_item['ADMIN_TAGS_ITEM_TITLE'] = cot::$L['Deleted'];
                    }

                    $tt->parse('MAIN.ADMIN_TAGS_ROW.ADMIN_TAGS_ROW_ITEMS');
                }
            } elseif ($k == 'forum') {

            }
        }
    }
		$tt->assign(array(
			'ADMIN_TAGS_FORM_ACTION' => cot_url('admin', 'm=other&p=tags&d=' . $durl),
            'ADMIN_TAGS_DEL_URL' => cot_url('admin', [
                'm' => 'other',
                'p' => 'tags',
                'a' => 'delete',
                'tag' => str_replace(' ', '_', $row['tag']),
                'x' => cot::$sys['xk']
            ]),
			'ADMIN_TAGS_CODE' => $row['tag'],
			'ADMIN_TAGS_TAG' => cot_inputbox('text', 'tag', htmlspecialchars_decode($row['tag']), array('maxlength' => '255')),//['.$row['tag'].']
			'ADMIN_TAGS_AREA' => implode(', ', $tagArea),
			'ADMIN_TAGS_COUNT' => $row['tag_cnt'],
			'ADMIN_TAGS_ITEMS' => str_replace(array('pages:', ','), array('', ', '), $row['tag_grp']),
			'ADMIN_TAGS_ODDEVEN' => cot_build_oddeven($ii)
		));
		/* === Hook - Part2 : Include === */
		foreach ($extp as $pl) {
			include $pl;
		}
		/* ===== */
		$tt->parse('MAIN.ADMIN_TAGS_ROW');
		$ii++;
}

$tt->assign(array(
	'ADMIN_TAGS_CONFIG_URL' => cot_url('admin', 'm=config&n=edit&o=plug&p=tags'),
	'ADMIN_TAGS_ADMINWARNINGS' => $adminwarnings,
	'ADMIN_TAGS_FORM_ACTION' => cot_url('admin', 'm=other&p=tags'),
	'ADMIN_TAGS_ORDER' => cot_selectbox($sorttype, 'sorttype', array_keys($sort_type), array_values($sort_type), false),
	'ADMIN_TAGS_WAY' => cot_selectbox($sortway, 'sortway', array_keys($sort_way), array_values($sort_way), false),
	'ADMIN_TAGS_FILTER' => cot_selectbox($filter, 'filter', array_keys($filter_type), array_values($filter_type), false),
	'ADMIN_TAGS_PAGINATION_PREV' => $pagenav['prev'],
	'ADMIN_TAGS_PAGNAV' => $pagenav['main'],
	'ADMIN_TAGS_PAGINATION_NEXT' => $pagenav['next'],
	'ADMIN_TAGS_TOTALITEMS' => $totalitems,
	'ADMIN_TAGS_COUNTER_ROW' => $ii
));

/* === Hook  === */
foreach (cot_getextplugins('admin.tags.tags') as $pl) {
	include $pl;
}
/* ===== */

cot_display_messages($tt);

$tt->parse('MAIN');
$adminmain = $tt->text('MAIN');
