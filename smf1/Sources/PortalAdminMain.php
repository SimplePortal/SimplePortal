<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_admin_main()
		// !!!

	void sportal_admin_config_main()
		// !!!

	void sportal_admin_general_settings()
		// !!!

	void sportal_admin_block_settings()
		// !!!

	void sportal_admin_article_settings()
		// !!!

	void sportal_information()
		// !!!
*/

function sportal_admin_main()
{
	global $context, $txt, $scripturl, $sourcedir;

	adminIndex('sportal');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	if (loadLanguage('SPortalAdmin', '', false) === false)
		loadLanguage('SPortalAdmin', 'english');

	if (loadLanguage('SPortalHelp', '', false) === false)
		loadLanguage('SPortalHelp', 'english');

	$areas = array(
		'portalconfig' => array('', 'sportal_admin_config_main'),
		'portalblocks' => array('PortalAdminBlocks.php', 'sportal_admin_blocks_main'),
		'portalarticles' => array('PortalAdminArticles.php', 'sportal_admin_articles_main'),
		'portalpages' => array('PortalAdminPages.php', 'sportal_admin_pages_main'),
		'portalshoutbox' => array('PortalAdminShoutbox.php', 'sportal_admin_shoutbox_main'),
	);

	$_REQUEST['area'] = isset($_REQUEST['area']) && isset($areas[$_REQUEST['area']]) ? $_REQUEST['area'] : 'portalconfig';
	$context['admin_area'] = $_REQUEST['area'];

	if (!empty($areas[$_REQUEST['area']][0]))
		require_once($sourcedir . '/' . $areas[$_REQUEST['area']][0]);

	$areas[$_REQUEST['area']][1]();
}

function sportal_admin_config_main()
{
	global $context, $txt, $scripturl, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_settings');

	require_once($sourcedir . '/ManageServer.php');
	loadTemplate('PortalAdmin');

	$subActions = array(
		'information' => 'sportal_information',
		'generalsettings' => 'sportal_admin_general_settings',
		'blocksettings' => 'sportal_admin_block_settings',
		'articlesettings' => 'sportal_admin_article_settings',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'information';

	$context['admin_tabs'] = array(
		'title' => $txt['sp-adminConfiguration'],
		'help' => 'sp_ConfigurationArea',
		'description' => $txt['sp-adminConfigurationDesc'],
		'tabs' => array(
			'information' => array(
				'title' => $txt['sp-info_title'],
				'description' => $txt['sp-info_desc'],
				'href' => $scripturl . '?action=manageportal;area=portalconfig;sa=information',
				'is_selected' => $_REQUEST['sa'] == 'information',
			),
			'generalsettings' => array(
				'title' => $txt['sp-adminGeneralSettingsName'],
				'description' => $txt['sp-adminConfigurationDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalconfig;sa=generalsettings',
				'is_selected' => $_REQUEST['sa'] == 'generalsettings',
			),
			'blocksettings' => array(
				'title' => $txt['sp-adminBlockSettingsName'],
				'description' => $txt['sp-adminConfigurationDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalconfig;sa=blocksettings',
				'is_selected' => $_REQUEST['sa'] == 'blocksettings',
			),
			'articlesettings' => array(
				'title' => $txt['sp-adminArticleSettingsName'],
				'description' => $txt['sp-adminConfigurationDesc'],
				'href' => $scripturl . '?action=manageportal;area=portalconfig;sa=articlesettings',
				'is_selected' => $_REQUEST['sa'] == 'articlesettings',
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function sportal_admin_general_settings()
{
	global $db_prefix, $context, $scripturl, $txt;

	$request = db_query("
		SELECT ID_THEME, value AS name
		FROM {$db_prefix}themes
		WHERE variable = 'name'
			AND ID_MEMBER = 0
		ORDER BY ID_THEME", __FILE__, __LINE__);
	$context['sp_themes'] = array('0' => &$txt['portalthemedefault']);
	while ($row = mysql_fetch_assoc($request))
		$context['sp_themes'][$row['ID_THEME']] = $row['name'];
	mysql_free_result($request);

	$config_vars = array(
			array('select', 'sp_portal_mode', explode('|', $txt['sp_portal_mode_options'])),
			array('check', 'sp_maintenance'),
			array('text', 'sp_standalone_url'),
		'',
			array('select', 'portaltheme', $context['sp_themes']),
			array('check', 'sp_disableColor'),
			array('check', 'sp_disableForumRedirect'),
			array('check', 'sp_disable_random_bullets'),
			array('check', 'sp_disable_php_validation'),
			array('check', 'sp_disable_side_collapse'),
			array('check', 'sp_resize_images'),
	);

	if (isset($_GET['save']))
	{
		checkSession();

		saveDBSettings($config_vars);
		redirectexit('action=manageportal;area=portalconfig;sa=generalsettings');
	}

	$context['post_url'] = $scripturl . '?action=manageportal;area=portalconfig;sa=generalsettings;save';
	$context['settings_title'] = $txt['sp-adminGeneralSettingsName'];
	$context['page_title'] = $txt['sp-adminGeneralSettingsName'];
	$context['sub_template'] = 'general_settings';

	prepareDBSettingContext($config_vars);
}

function sportal_admin_block_settings()
{
	global $context, $scripturl, $txt;

	$config_vars = array(
			array('check', 'showleft'),
			array('check', 'showright'),
			array('text', 'leftwidth'),
			array('text', 'rightwidth'),
		'',
			array('check', 'sp_enableIntegration'),
			array(array('sp_adminIntegrationHide' => $txt[2], 'sp_profileIntegrationHide' => $txt[79], 'sp_pmIntegrationHide' => $txt[144], 'sp_mlistIntegrationHide' => $txt[19], 'sp_searchIntegrationHide' => $txt[182], 'sp_calendarIntegrationHide' => $txt['calendar24']), 'sp_IntegrationHide'),
	);

	if (isset($_GET['save']))
	{
		checkSession();

		$width_checkup = array('left', 'right');
		foreach ($width_checkup as $pos)
		{
			if (!empty($_POST[$pos . 'width']))
			{
				if (stripos($_POST[$pos . 'width'], 'px') !== false)
					$suffix = 'px';
				elseif (strpos($_POST[$pos . 'width'], '%') !== false)
					$suffix = '%';
				else
					$suffix = '';

				preg_match_all('/(?:([0-9]+)|.)/i', $_POST[$pos . 'width'], $matches);

				$number = (int) implode('', $matches[1]);
				if(!empty($number) && $number > 0)
					$_POST[$pos . 'width'] = $number . $suffix;
				else
					$_POST[$pos . 'width'] = '';
			}
			else
				$_POST[$pos . 'width'] = '';
		}

		unset($config_vars[7]);
		$config_vars = array_merge(
			$config_vars,
			array(
				array('check', 'sp_adminIntegrationHide'),
				array('check', 'sp_profileIntegrationHide'),
				array('check', 'sp_pmIntegrationHide'),
				array('check', 'sp_mlistIntegrationHide'),
				array('check', 'sp_searchIntegrationHide'),
				array('check', 'sp_calendarIntegrationHide'),
			)
		);

		saveDBSettings($config_vars);
		redirectexit('action=manageportal;area=portalconfig;sa=blocksettings');
	}

	$context['post_url'] = $scripturl . '?action=manageportal;area=portalconfig;sa=blocksettings;save';
	$context['settings_title'] = $txt['sp-adminBlockSettingsName'];
	$context['page_title'] = $txt['sp-adminBlockSettingsName'];
	$context['sub_template'] = 'general_settings';

	prepareDBSettingContext($config_vars);
}

function sportal_admin_article_settings()
{
	global $context, $scripturl, $txt;

	$config_vars = array(
			array('check', 'articleactive'),
			array('int', 'articleperpage'),
			array('int', 'articlelength'),
			array('check', 'articleavatar'),
	);

	if (isset($_GET['save']))
	{
		checkSession();

		saveDBSettings($config_vars);
		redirectexit('action=manageportal;area=portalconfig;sa=articlesettings');
	}

	$context['post_url'] = $scripturl . '?action=manageportal;area=portalconfig;sa=articlesettings;save';
	$context['settings_title'] = $txt['sp-adminArticleSettingsName'];
	$context['page_title'] = $txt['sp-adminArticleSettingsName'];
	$context['sub_template'] = 'general_settings';

	prepareDBSettingContext($config_vars);
}

function sportal_information($in_admin = true)
{
	global $context, $scripturl, $txt, $sourcedir, $sportal_version, $user_profile;

	$context['sp_credits'] = array(
		array(
			'pretext' => $txt['sp-info_intro'],
			'title' => $txt['sp-info_team'],
			'groups' => array(
				array(
					'title' => $txt['sp-info_groups_pm'],
					'members' => array(
						'Eliana Tamerin',
						'Huw',
					),
				),
				array(
					'title' => $txt['sp-info_groups_dev'],
					'members' => array(
						'<span onclick="if (getInnerHTML(this).indexOf(\'Sinan\') == -1) setInnerHTML(this, \'Sinan &quot;[SiNaN]&quot; &Ccedil;evik\'); return false;">Selman &quot;[SiNaN]&quot; Eser</span>',
						'Nathaniel Baxter',
						'&#12487;&#12451;&#12531;1031',
						'spuds',
						'emanuele',
					),
				),
				array(
					'title' => $txt['sp-info_groups_support'],
					'members' => array(
						'<span onclick="if (getInnerHTML(this).indexOf(\'Queen\') == -1) setInnerHTML(this, \'Angelina &quot;Queen of Support&quot; Belle\'); return false;">AngelinaBelle</span>',
						'Chen Zhen',
						'andy',
						'Ninja ZX-10RR',
						'phantomm',
					),
				),
				array(
					'title' => $txt['sp-info_groups_customize'],
					'members' => array(
						'Blue',
						'Berat &quot;grafitus&quot; Do&#287;an',
						'Robbo',
					),
				),
				array(
					'title' => $txt['sp-info_groups_language'],
					'members' => array(
						'Kryzen',
						'Jade &quot;Alundra&quot; Elizabeth',
						'<span onclick="if (getInnerHTML(this).indexOf(\'King\') == -1) setInnerHTML(this, \'130 &quot;King of Pirates&quot; 860\'); return false;">130860</span>',
					),
				),
				array(
					'title' => $txt['sp-info_groups_marketing'],
					'members' => array(
						'BryanD',
					),
				),
				array(
					'title' => $txt['sp-info_groups_beta'],
					'members' => array(
						'BurkeKnight',
						'ARG',
						'Old Fossil',
						'David',
						'sharks',
						'Willerby',
						'&#214;zg&#252;r',
						'c23_Mike',
					),
				),
			),
		),
		array(
			'title' => $txt['sp-info_special'],
			'posttext' => $txt['sp-info_anyone'],
			'groups' => array(
				array(
					'title' => $txt['sp-info_groups_translators'],
					'members' => array(
						$txt['sp-info_translators_message'],
					),
				),
				array(
					'title' => $txt['sp-info_groups_founder'],
					'members' => array(
					),
				),
				array(
					'title' => $txt['sp-info_groups_orignal_pm'],
					'members' => array(
					),
				),
				array(
					'title' => $txt['sp-info_fam_fam'],
					'members' => array(
						$txt['sp-info_fam_fam_message'],
					),
				),
			),
		),
	);

	if (!$in_admin)
	{
		loadTemplate('PortalAdmin');
		$context['robot_no_index'] = true;
		$context['in_admin'] = false;
	}
	else
	{
		$context['in_admin'] = true;
		$context['sp_version'] = $sportal_version;
		$context['sp_managers'] = array();

		require_once($sourcedir . '/Subs-Members.php');
		$manager_ids = loadMemberData(membersAllowedTo('sp_admin'), false, 'minimal');

		if ($manager_ids)
			foreach ($manager_ids as $member)
				$context['sp_managers'][] = '<a href="' . $scripturl . '?action=profile;u=' . $user_profile[$member]['ID_MEMBER'] . '">' . $user_profile[$member]['realName'] . '</a>';
	}

	$context['sub_template'] = 'information';
	$context['page_title'] = $txt['sp-info_title'];
}

?>