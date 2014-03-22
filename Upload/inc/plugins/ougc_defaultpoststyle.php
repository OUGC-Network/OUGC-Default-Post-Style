<?php

/***************************************************************************
 *
 *   OUGC Default Post Style plugin
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://omarg.me
 *
 *   Allow users to set a default style for their posts/messages.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run our hook.
if(!defined('IN_ADMINCP'))
{
	global $plugins, $mybb;
	define('OUGC_DPS', '{POST_CONTENT}');

	$plugins->add_hook('usercp_menu_built', 'ougc_defaultpoststyle_menu');
	$plugins->add_hook('usercp_start', 'ougc_defaultpoststyle_usercp');

	// Now, where should we use this...?
	$plugins->add_hook('newreply_do_newreply_start', 'ougc_defaultpoststyle_default');
	$plugins->add_hook('newthread_do_newthread_start', 'ougc_defaultpoststyle_default');
	$plugins->add_hook('private_send_do_send', 'ougc_defaultpoststyle_default');
	$plugins->add_hook('calendar_do_addevent_start', 'ougc_defaultpoststyle_default');
	$plugins->add_hook('comment_download_start', 'ougc_defaultpoststyle_default');

	if(in_array(THIS_SCRIPT, array('usercp.php', 'usercp2.php', 'private.php')))
	{
		global $templatelist;

		if(isset($templatelist))
		{
			$templatelist .= ',';
		}
		$templatelist .= 'ougc_defaultpoststyle_menu';

		if($mybb->input['action'] == 'defaultpoststyle')
		{
			$templatelist .= ', ougc_defaultpoststyle, ougc_defaultpoststyle_preview, smilieinsert_getmore, smilieinsert, codebuttons';
		}
	}
}

//Necessary plugin information for the ACP plugin manager.
function ougc_defaultpoststyle_info()
{
	global $lang;
	isset($lang->ougc_defaultpoststyle) or $lang->load('ougc_defaultpoststyle');

	return array(
		'name'			=> 'OUGC Default Post Style',
		'description'	=> $lang->ougc_defaultpoststyle_d,
		'website'		=> 'http://udezain.com.ar/',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://udezain.com.ar/',
		'version'		=> '1.0',
		'compatibility'	=> '16*'
	);
}

//Activate the plugin.
function ougc_defaultpoststyle_activate()
{
	global $db, $lang;
	isset($lang->ougc_defaultpoststyle) or $lang->load('ougc_defaultpoststyle');
	ougc_defaultpoststyle_deactivate();

	$db->insert_query('templates', array(
		'title'		=>	'ougc_defaultpoststyle_menu',
		'template'	=>	$db->escape_string('<tr><td class="trow1 smalltext"><a href="{$mybb->settings[\'bburl\']}/usercp.php?action=defaultpoststyle" class="usercp_nav_item usercp_nav_defaultpoststyle" style="background: url(\'images/icons/pencil.gif\') no-repeat left center;">{$lang->ougc_defaultpoststyle_nav}</a></td></tr>'),
		'sid'		=>	-1,
	));
	$db->insert_query('templates', array(
		'title'		=>	'ougc_defaultpoststyle',
		'template'	=>	$db->escape_string('<html>
<head>
<title>{$lang->ougc_defaultpoststyle_nav} | {$mybb->settings[\'bbname\']}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
{$usercpnav}
<td valign="top">
{$preview}
{$errors}
<form action="{$mybb->settings[\'bburl\']}/usercp.php?action=defaultpoststyle" method="post" enctype="multipart/form-data" name="input">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->ougc_defaultpoststyle_nav}</strong></td>
</tr>
<tr>
<td class="trow1" valign="top"><strong>{$lang->ougc_defaultpoststyle_message}</strong>{$smilieinserter}</td>
<td class="trow1" valign="top"><textarea name="message" id="message" rows="20" cols="70" tabindex="2">{$mybb->input[\'message\']}</textarea>{$codebuttons}</td>
</tr>
</table>
<br />
<div align="center">
<input type="submit" class="button" name="save" value="{$lang->ougc_defaultpoststyle_save}" tabindex="3" accesskey="s" />  <input type="submit" class="button" name="preview" value="{$lang->ougc_defaultpoststyle_preview}" tabindex="4" />
</div>
</form>
</tr>
</table>
{$footer}
</body>
</html>'),
		'sid'		=>	-1,
	));
	$db->insert_query('templates', array(
		'title'		=>	'ougc_defaultpoststyle_preview',
		'template'	=>	$db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->ougc_defaultpoststyle_preview}</strong></td>
</tr>
<tr>
<td class="trow1" valign="top">{$preview}</td>
</tr>
</table>
<br />'),
		'sid'		=>	-1,
	));

	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('usercp_nav_misc', '#'.preg_quote('="usercpmisc_e">').'#', '="usercpmisc_e"><!--OUGC_DPS-->');

	// Add our settings group.
	$gid = $db->insert_query('settinggroups', 
		array(
			'name'			=> 'ougc_defaultpoststyle',
			'title'			=> $db->escape_string($lang->ougc_defaultpoststyle_sg),
			'description'	=> $db->escape_string($lang->ougc_defaultpoststyle_d),
			'disporder'		=> 99,
			'isdefault'		=> 'no'
		)
	);
	$gid = intval($gid);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_on'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_on),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_on_d),
			'optionscode'	=>	'onoff',
			'value'			=>	1,
			'disporder'		=>	1,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_groups'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_groups),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_groups_d),
			'optionscode'	=>	'text',
			'value'			=>	'3,4,5',
			'disporder'		=>	2,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_limit'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_limit),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_limit_d),
			'optionscode'	=>	'text',
			'value'			=>	100,
			'disporder'		=>	3,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_update'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_update),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_update_d),
			'optionscode'	=>	'yesno',
			'value'			=>	1,
			'disporder'		=>	4,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_forums'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_forums),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_forums_d),
			'optionscode'	=>	'text',
			'value'			=>	'',
			'disporder'		=>	5,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_private'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_private),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_private_d),
			'optionscode'	=>	'yesno',
			'value'			=>	'',
			'disporder'		=>	6,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_calendar'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_calendar),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_calendar_d),
			'optionscode'	=>	'yesno',
			'value'			=>	'',
			'disporder'		=>	7,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	$db->escape_string('ougc_defaultpoststyle_mydownloads'),
			'title'			=>	$db->escape_string($lang->ougc_defaultpoststyle_mydownloads),
			'description'	=>	$db->escape_string($lang->ougc_defaultpoststyle_mydownloads_d),
			'optionscode'	=>	'yesno',
			'value'			=>	'',
			'disporder'		=>	8,
			'gid'			=>	$gid
		)
	);
}

//Deactivate the plugin.
function ougc_defaultpoststyle_deactivate()
{
	global $db;

	$db->delete_query('templates', "title IN('ougc_defaultpoststyle_menu', 'ougc_defaultpoststyle_preview')");

	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('usercp_nav_misc', '#'.preg_quote('<!--OUGC_DPS-->').'#', '', 0);

	$gid = $db->fetch_field($db->simple_select('settinggroups', 'gid', 'name="ougc_defaultpoststyle"'), 'gid');
	if($gid)
	{
		$db->delete_query("settings", "gid='{$gid}'");
		$db->delete_query("settinggroups", "gid='{$gid}'");
		rebuild_settings();
	}
}

function ougc_defaultpoststyle_install()
{
	global $db;
	ougc_defaultpoststyle_uninstall();

	$db->add_column('users', 'ougc_dps', "varchar(255) NOT NULL DEFAULT ''");
}

function ougc_defaultpoststyle_uninstall()
{
	global $db;

	if($db->field_exists('ougc_dps', 'users'))
	{
		$db->drop_column('users', 'ougc_dps');
	}
}

function ougc_defaultpoststyle_is_installed()
{
	global $db;

	return ($db->field_exists('ougc_dps', 'users'));
}
function ougc_defaultpoststyle_menu()
{
	global $mybb;

	// Disabled
	if($mybb->settings['ougc_defaultpoststyle_on'] != 1)
	{
		return;
	}

	if(ougc_check_groups($mybb->settings['ougc_defaultpoststyle_groups']))
	{
		global $usercpnav, $lang, $templates;
		isset($lang->ougc_defaultpoststyle) or $lang->load('ougc_defaultpoststyle');

		eval('$menu = "'.$templates->get('ougc_defaultpoststyle_menu').'";');
		$usercpnav = str_replace('<!--OUGC_DPS-->', $menu, $usercpnav);

		return $usercpnav;
	}
}

function ougc_defaultpoststyle_usercp()
{
	global $mybb;

	// Wrong area
	if($mybb->input['action'] != 'defaultpoststyle')
	{
		return;
	}

	// Disabled
	if($mybb->settings['ougc_defaultpoststyle_on'] != 1)
	{
		error_no_permission();
	}

	// Invalid group
	if(!ougc_check_groups($mybb->settings['ougc_defaultpoststyle_groups']))
	{
		error_no_permission();
	}

	global $templates, $lang, $headerinclude, $header, $footer, $usercpnav, $theme;
	isset($lang->ougc_defaultpoststyle) or $lang->load('ougc_defaultpoststyle');

	// Our navigations
	add_breadcrumb($lang->nav_usercp, "{$mybb->settings['bburl']}/usercp.php");
	add_breadcrumb($lang->ougc_defaultpoststyle_nav);

	// Default content for (new users) / (empty content)
	if($mybb->request_method != 'post' && ($mybb->user['ougc_dps'] || !$mybb->input['message']))
	{
		$mybb->input['message'] = $mybb->user['ougc_dps'];
		if(!$mybb->input['message'])
		{
			$mybb->input['message'] = OUGC_DPS;
		}
	}

	// Sutmit process
	if($mybb->request_method == 'post')
	{
		// Just verify if this is a valid post input
		verify_post_check($mybb->input['my_post_key']);

		// Users need to include "OUGC_DPS" into the message
		$errors = array();
		if(!my_strpos($mybb->input['message'], OUGC_DPS) && $mybb->input['message'] != '')
		{
			$errors[] = $lang->sprintf($lang->ougc_defaultpoststyle_missingval, OUGC_DPS);
		}

		// We have a 200 characters limit, even if admin setted longer.
		$limit = intval($mybb->settings['ougc_defaultpoststyle_limit']);
		$limit = ($limit < 1 || $limit > 250 ? 100 : $limit);
		if(my_strlen($mybb->input['message']) > $limit)
		{
			$errors[] = $lang->sprintf($lang->ougc_defaultpoststyle_limit, my_number_format($limit));
		}

		// There are errors..
		if($errors)
		{
			$errors = inline_error($errors);
		}
		elseif($mybb->input['preview'])
		{
			global $parser;
			// Set up the parser options.
			$parser_options = array(
				'allow_html' => 0,
				'allow_mycode' => 1,
				'allow_smilies' => 1,
				'allow_imgcode' => 0,
				'allow_videocode' => 0,
				'filter_badwords' => 1
			);

			$preview = $parser->parse_message($mybb->input['message'], $parser_options);
			eval('$preview = "'.$templates->get('ougc_defaultpoststyle_preview').'";');
		}
		// Everything is alright, save it
		else
		{
			global $db;
			if($mybb->input['message'] == OUGC_DPS)
			{
				$mybb->input['message'] = '';
			}

			$db->update_query('users', array('ougc_dps' => $db->escape_string($mybb->input['message'])), "uid='{$mybb->user['uid']}'");
			redirect("{$mybb->settings['bburl']}/usercp.php", $lang->ougc_defaultpoststyle_redirect);
		}
	}
	if(is_array($errors))
	{
		$errors = '';
	}
	if(!$preview)
	{
		$preview = '';
	}

	// Fancy stuff
	$smilieinserter = $codebuttons = '';
	if($mybb->settings['smilieinserter'] == 1)
	{
		$smilieinserter = build_clickable_smilies();
	}
	if($mybb->settings['bbcodeinserter'] == 1)
	{
		$codebuttons = build_mycode_inserter();
	}

	eval('$page = "'.$templates->get('ougc_defaultpoststyle').'";');
	output_page($page);
	exit;
}

function ougc_defaultpoststyle_default()
{
	global $mybb, $settings;

	// Private Messages
	if(THIS_SCRIPT == 'private.php')
	{
		if($settings['ougc_defaultpoststyle_private'] == 1)
		{
			$mybb->input['message'] = ougc_defaultpoststyle_split($mybb->input['message']);
		}
		return $mybb;
	}

	// Calendar
	if(THIS_SCRIPT == 'calendar.php')
	{
		if($settings['ougc_defaultpoststyle_calendar'] == 1)
		{
			$mybb->input['description'] = ougc_defaultpoststyle_split($mybb->input['description']);
		}
		return $mybb;
	}

	// Calendar
	if(THIS_SCRIPT == 'mydownloads/comment_download.php')
	{
		if($settings['ougc_defaultpoststyle_mydownloads'] == 1)
		{
			$mybb->input['message'] = ougc_defaultpoststyle_split($mybb->input['message']);
		}
		return $mybb;
	}

	$mybb->input['message'] = ougc_defaultpoststyle_split($mybb->input['message'], $GLOBALS['fid']);
	return $mybb;
}

// Sooo, save us time god of light!!
function ougc_defaultpoststyle_split($message, $fid=0)
{
	global $mybb;

	// Disabled
	if($mybb->settings['ougc_defaultpoststyle_on'] != 1)
	{
		return $message;
	}

	// Invalid group
	if(!ougc_check_groups($mybb->settings['ougc_defaultpoststyle_groups']))
	{
		$update = true;
	}

	// For some reason this user's DPS seems to be invalid...
	if(!my_strpos($mybb->user['ougc_dps'], OUGC_DPS))
	{
		$update = true;
	}

	// Invalid for some reason
	if($update)
	{
		// Should we remove/update it?
		if($mybb->settings['ougc_defaultpoststyle_update'] == 1)
		{
			global $db;

			$db->update_query('users', array('ougc_dps' => ''), "uid='{$mybb->user['uid']}'");
		}
		return $message;
	}

	// Invalid forum.
	if($fid && !empty($mybb->settings['ougc_defaultpoststyle_forums']))
	{
		$forums = explode(',', $mybb->settings['ougc_defaultpoststyle_forums']);
		$forums = array_map('intval', $forums);
		if(in_array($fid, $forums))
		{
			return $message;
		}
	}

	// Split the thing
	$dps = preg_split("#".OUGC_DPS."#", $mybb->user['ougc_dps']);
	if($dps[0] && $dps[1])
	{
		return $dps[0].$message.$dps[1];
	}
	return $message;
}

// This will check current user's groups.
if(!function_exists('ougc_check_groups'))
{
	function ougc_check_groups($groups, $empty=true)
	{
		global $mybb;
		if(empty($groups) && $empty == true)
		{
			return true;
		}
		if(!empty($mybb->user['additionalgroups']))
		{
			$usergroups = explode(',', $mybb->user['additionalgroups']);
		}
		if(!is_array($usergroups))
		{
			$usergroups = array();
		}
		$usergroups[] = $mybb->user['usergroup'];
		$groups = explode(',', $groups);
		foreach($usergroups as $gid)
		{
			if(in_array($gid, $groups))
			{
				return true;
			}
		}
		return false;
	}
}