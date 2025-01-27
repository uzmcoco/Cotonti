<?php
/**
 * Install script
 *
 * @package Install
 * @copyright (c) Cotonti Team
 * @license https://github.com/Cotonti/Cotonti/blob/master/License.txt
 */

// Environment setup
define('COT_CODE', TRUE);
define('COT_INSTALL', TRUE);
//define('COT_ADMIN', TRUE);

if (file_exists('./datas/config.php'))
{
	require_once './datas/config.php';
}
else
{
	require_once './datas/config-sample.php';
}

if (empty($cfg['modules_dir']))
{
	$cfg['modules_dir'] = './modules';
}
if (empty($cfg['lang_dir']))
{
	$cfg['lang_dir'] = './lang';
}

// Force config options
$cfg['display_errors'] = true;
$cfg['debug_mode'] = true;
$cfg['customfuncs'] = false;
$cfg['cache'] = false;
$cfg['xtpl_cache'] = false;

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

require_once $cfg['system_dir'].'/functions.php';
require_once $cfg['system_dir'].'/cotemplate.php';
require_once 'system/debug.php';

date_default_timezone_set('UTC');
$sys['now'] = time();

$env['location'] = 'install';
$env['ext'] = 'install';

if (isset($cfg['new_install']) && $cfg['new_install'])
{
	require_once $cfg['system_dir'].'/database.php';

	// A Few basics from common.php
	if (version_compare(PHP_VERSION, '6.0.0', '<='))
	{
		if (get_magic_quotes_gpc())
		{
			function cot_disable_mqgpc(&$value, $key)
			{
				$value = stripslashes($value);
			}
			$gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
			array_walk_recursive($gpc, 'cot_disable_mqgpc');
		}
	}
	define('MQGPC', FALSE);
	error_reporting(E_ALL ^ E_NOTICE);

	session_start();

    cot::init();

    // It will be needed when we start to install extensions
    // Getting the server-relative path
    $url = parse_url($cfg['mainurl']);
	$sys['secure'] = $url['scheme'] == 'https' ? true : false;
	$sys['scheme'] = $url['scheme'];
	$sys['site_uri'] = isset($url['path']) ? $url['path'] : '';
	$sys['host'] = $url['host'];
	$sys['domain'] = preg_replace('#^www\.#', '', $url['host']);
    $sys['site_uri'] = rtrim($sys['site_uri'], '/').'/';
	$sys['port'] = empty($url['port']) ? '' : ':' . $url['port'];
	$sys['abs_url'] = $url['scheme'] . '://' . $sys['host'] . $sys['port'] . $sys['site_uri'];

    $sys['site_id'] = 'install';

	// Installer language selection support
	if (empty($_SESSION['cot_inst_lang'])) {
		$lang = cot_import('lang', 'P', 'ALP');
		if (empty($lang)) {
			$lang = cot_lang_determine();
		}
	} else {
		$lang = $_SESSION['cot_inst_lang'];
	}

	require_once cot_langfile('main', 'core');
	require_once $cfg['system_dir'] . '/resources.rc.php';
} else {
	$branch = 'siena';
	$prev_branch = 'genoa';

	require_once $cfg['system_dir'].'/database.php';

    $db = new CotDB([
        'host' => $cfg['mysqlhost'],
        'port' => !empty($cfg['mysqlport']) ? $cfg['mysqlport'] : null,
        'tablePrefix' => $db_x,
        'user' => $cfg['mysqluser'],
        'password' => $cfg['mysqlpassword'],
        'dbName' => $cfg['mysqldb'],
        'charset' => !empty($cfg['mysqlcharset']) ? $cfg['mysqlcharset'] : null,
        'collate' => !empty($cfg['mysqlcollate']) ? $cfg['mysqlcollate'] : null,
    ]);

	cot::init();

	$sql_install = @$db->query("SHOW TABLES LIKE '$db_updates'");

	if ($sql_install->rowCount() != 1) {
		define('COT_UPGRADE', true);
		$cfg['defaulttheme'] = 'nemesis';
		$cfg['defaultscheme'] = 'default';
	}
	require_once $cfg['system_dir'] . '/common.php';
}

require_once cot_incfile('forms');
require_once cot_incfile('extensions');
require_once cot_langfile('install', 'module');
require_once cot_langfile('users', 'core');
require_once cot_langfile('admin', 'core');

require_once cot_incfile('install', 'module', 'resources');

// Various Generic Vars needed to operate as Normal
$theme = $cfg['defaulttheme'];
$scheme = $cfg['defaultscheme'];
$out['meta_lastmod'] = gmdate('D, d M Y H:i:s');
$file['config'] = './datas/config.php';
$file['config_sample'] = './datas/config-sample.php';
$file['sql'] = './setup/install.sql';

if (!$cfg['new_install']) {
	include cot_incfile('install', 'module', 'update');
} else {
	include cot_incfile('install', 'module', 'install');
}
