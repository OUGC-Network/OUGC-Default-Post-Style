<?php

/***************************************************************************
 *
 *   OUGC Default Post Style plugin (/inc/plugins/ougc_defaultpoststyle.php)
 *     Author: Omar Gonzalez
 *   Copyright: © 2012-2014 Omar Gonzalez
 *
 *   Website: http://omarg.me
 *
 *   Allow users to set a default style for their posts/messages.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

global $plugins, $mybb;

// Run/Add Hooks
if (defined('IN_ADMINCP')) {
    $plugins->add_hook('admin_config_settings_start', 'ougc_defaultpoststyle_lang_load');
    $plugins->add_hook('admin_style_templates_set', 'ougc_defaultpoststyle_lang_load');
    $plugins->add_hook('admin_config_settings_change', 'ougc_defaultpoststyle_settings_change');
} else {
    $plugins->add_hook('usercp_menu_built', 'ougc_defaultpoststyle_menu');
    $plugins->add_hook('usercp_start', 'ougc_defaultpoststyle_usercp');

    $plugins->add_hook('newreply_do_newreply_start', 'ougc_defaultpoststyle_run');
    $plugins->add_hook('newthread_do_newthread_start', 'ougc_defaultpoststyle_run');
    $plugins->add_hook('private_send_do_send', 'ougc_defaultpoststyle_run');
    $plugins->add_hook('calendar_do_addevent_start', 'ougc_defaultpoststyle_run');
    $plugins->add_hook('comment_download_start', 'ougc_defaultpoststyle_run');

    if (in_array(constant('THIS_SCRIPT'), array('usercp.php', 'usercp2.php', 'private.php'))) {
        global $templatelist;

        if (!isset($templatelist)) {
            $templatelist = '';
        } else {
            $templatelist .= ',';
        }

        $templatelist .= 'ougc_defaultpoststyle_menu';

        if ($mybb->input['action'] == 'defaultpoststyle') {
            $templatelist .= ',ougc_defaultpoststyle,ougc_defaultpoststyle_preview,ougc_defaultpoststyle_newpoints, smilieinsert_getmore,smilieinsert,codebuttons';
        }
    }
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', constant('MYBB_ROOT') . 'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_defaultpoststyle_info()
{
    global $lang;
    ougc_defaultpoststyle_lang_load();

    return array(
        'name' => 'OUGC Default Post Style',
        'description' => $lang->setting_group_ougc_defaultpoststyle_desc,
        'website' => 'http://omarg.me',
        'author' => 'Omar G.',
        'authorsite' => 'http://omarg.me',
        'version' => '1.1',
        'versioncode' => 1100,
        'compatibility' => '18*',
        'guid' => '59241ab4cbb502a720aa7a995545eb51',
        'pl' => array(
            'version' => 12,
            'url' => 'http://mods.mybb.com/view/pluginlibrary'
        )
    );
}

// _activate
function ougc_defaultpoststyle_activate()
{
    global $PL, $lang, $cache;
    ougc_defaultpoststyle_lang_load();
    ougc_defaultpoststyle_deactivate();

    // Add settings group
    $PL->settings(
        'ougc_defaultpoststyle',
        $lang->setting_group_ougc_defaultpoststyle,
        $lang->setting_group_ougc_defaultpoststyle_desc,
        array(
            'limit' => array(
                'title' => $lang->setting_ougc_defaultpoststyle_limit,
                'description' => $lang->setting_ougc_defaultpoststyle_limit_desc,
                'optionscode' => 'text',
                'value' => 100,
            ),
            'string' => array(
                'title' => $lang->setting_ougc_defaultpoststyle_string,
                'description' => $lang->setting_ougc_defaultpoststyle_string_desc,
                'optionscode' => 'text',
                'value' => '{MESSAGE}',
            ),
            'groups' => array(
                'title' => $lang->setting_ougc_defaultpoststyle_groups,
                'description' => $lang->setting_ougc_defaultpoststyle_groups_desc,
                'optionscode' => 'text',
                'value' => '3,4,6',
            ),
            'forums' => array(
                'title' => $lang->setting_ougc_defaultpoststyle_forums,
                'description' => $lang->setting_ougc_defaultpoststyle_forums_desc,
                'optionscode' => 'text',
                'value' => '',
            ),
            'newpoints' => array(
                'title' => $lang->setting_ougc_defaultpoststyle_newpoints,
                'description' => $lang->setting_ougc_defaultpoststyle_newpoints_desc,
                'optionscode' => 'text',
                'value' => '',
            )
        )
    );

    // Add template group
    $PL->templates('ougcdefaultpoststyle', '<lang:setting_group_ougc_defaultpoststyle>', array(
        '' => '<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->ougc_defaultpoststyle_nav}</title>
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
{$newpoints}
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
</html>',
        'menu' => '<tr><td class="trow1 smalltext"><a href="{$mybb->settings[\'bburl\']}/usercp.php?action=defaultpoststyle" class="usercp_nav_item usercp_nav_defaultpoststyle" style="background: url(\'images/icons/pencil.gif\') no-repeat left center;">{$lang->ougc_defaultpoststyle_nav}</a></td></tr>',
        'preview' => '<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->ougc_defaultpoststyle_preview}</strong></td>
</tr>
<tr>
<td class="trow1" valign="top">{$preview}</td>
</tr>
</table>
<br />',
        'newpoints' => '<tr>
<td class="tcat" colspan="2"><strong class="smalltext">{$lang->ougc_defaultpoststyle_newpoints}</strong></td>
</tr>'
    ));

    // Modify templates
    require_once constant('MYBB_ROOT') . 'inc/adminfunctions_templates.php';
    find_replace_templatesets(
        'usercp_nav_misc',
        '#' . preg_quote('="usercpmisc_e">') . '#',
        '="usercpmisc_e"><!--OUGC_DEFAULTPOSTSTYLE-->'
    );

    // Insert/update version into cache
    $plugins = $cache->read('ougc_plugins');
    if (!$plugins) {
        $plugins = array();
    }

    $info = ougc_defaultpoststyle_info();

    if (!isset($plugins['defaultpoststyle'])) {
        $plugins['defaultpoststyle'] = $info['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    $plugins['defaultpoststyle'] = $info['versioncode'];
    $cache->update('ougc_plugins', $plugins);
}

// _deactivate
function ougc_defaultpoststyle_deactivate()
{
    ougc_defaultpoststyle_pl_check();

    // Revert template edits
    require_once constant('MYBB_ROOT') . 'inc/adminfunctions_templates.php';
    find_replace_templatesets('usercp_nav_misc', '#' . preg_quote('<!--OUGC_DEFAULTPOSTSTYLE-->') . '#', '', 0);
}

// install() routine
function ougc_defaultpoststyle_install()
{
    global $db;

    // Add DB entries
    if (!$db->field_exists('ougc_defaultpoststyle', 'users')) {
        $db->add_column('users', 'ougc_defaultpoststyle', 'varchar(255) NOT NULL DEFAULT \'\'');
    }
}

// _is_installed() routine
function ougc_defaultpoststyle_is_installed()
{
    global $db;

    return $db->field_exists('ougc_defaultpoststyle', 'users');
}

// _uninstall() routine
function ougc_defaultpoststyle_uninstall()
{
    global $db, $PL, $cache;
    ougc_defaultpoststyle_pl_check();

    // Drop DB entries
    if ($db->field_exists('ougc_defaultpoststyle', 'users')) {
        $db->drop_column('users', 'ougc_defaultpoststyle');
    }

    $PL->settings_delete('ougc_defaultpoststyle');
    $PL->templates_delete('ougcdefaultpoststyle');

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['defaultpoststyle'])) {
        unset($plugins['defaultpoststyle']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $PL->cache_delete('ougc_plugins');
    }
}

// Loads language strings
function ougc_defaultpoststyle_lang_load()
{
    global $lang;

    isset($lang->setting_group_ougc_defaultpoststyle) or $lang->load('ougc_defaultpoststyle');
}

// PluginLibrary dependency check & load
function ougc_defaultpoststyle_pl_check()
{
    global $lang;
    ougc_defaultpoststyle_lang_load();
    $info = ougc_defaultpoststyle_info();

    if (!file_exists(PLUGINLIBRARY)) {
        flash_message(
            $lang->sprintf($lang->ougc_defaultpoststyle_pl_required, $info['pl']['url'], $info['pl']['version']),
            'error'
        );
        admin_redirect('index.php?module=config-plugins');
        exit;
    }

    global $PL;

    $PL or require_once PLUGINLIBRARY;

    if ($PL->version < $info['pl']['version']) {
        flash_message(
            $lang->sprintf(
                $lang->ougc_defaultpoststyle_pl_old,
                $info['pl']['url'],
                $info['pl']['version'],
                $PL->version
            ),
            'error'
        );
        admin_redirect('index.php?module=config-plugins');
        exit;
    }
}

// Language support for settings
function ougc_defaultpoststyle_settings_change()
{
    global $db, $mybb;

    $query = $db->simple_select('settinggroups', 'name', 'gid=\'' . (int)$mybb->input['gid'] . '\'');
    $groupname = $db->fetch_field($query, 'name');
    if ($groupname == 'ougc_defaultpoststyle') {
        global $plugins;
        ougc_defaultpoststyle_lang_load();

        if ($mybb->request_method == 'post') {
            global $settings;

            $limit = &$mybb->input['upsetting']['ougc_defaultpoststyle_limit'];
            $limit = ($limit < 1 ? 1 : ($limit > 250 ? 250 : $limit));

            $gids = '';
            if (isset($mybb->input['ougc_defaultpoststyle_groups']) && is_array(
                    $mybb->input['ougc_defaultpoststyle_groups']
                )) {
                $gids = implode(
                    ',',
                    (array)array_filter(array_map('intval', $mybb->input['ougc_defaultpoststyle_groups']))
                );
            }

            $mybb->input['upsetting']['ougc_defaultpoststyle_groups'] = $gids;

            $fids = '';
            if (isset($mybb->input['ougc_defaultpoststyle_forums']) && is_array(
                    $mybb->input['ougc_defaultpoststyle_forums']
                )) {
                $fids = implode(
                    ',',
                    (array)array_filter(array_map('intval', $mybb->input['ougc_defaultpoststyle_forums']))
                );
            }

            $mybb->input['upsetting']['ougc_defaultpoststyle_forums'] = $fids;

            return;
        }

        $plugins->add_hook('admin_formcontainer_output_row', 'ougc_defaultpoststyle_formcontainer_output_row');
    }
}

// Friendly settings
function ougc_defaultpoststyle_formcontainer_output_row(&$args)
{
    if ($args['row_options']['id'] == 'row_setting_ougc_defaultpoststyle_groups') {
        global $form, $settings;

        $args['content'] = $form->generate_group_select(
            'ougc_defaultpoststyle_groups[]',
            explode(',', $settings['ougc_defaultpoststyle_groups']),
            array('multiple' => true, 'size' => 5)
        );
    }
    if ($args['row_options']['id'] == 'row_setting_ougc_defaultpoststyle_forums') {
        global $form, $settings;

        $args['content'] = $form->generate_forum_select(
            'ougc_defaultpoststyle_forums[]',
            explode(',', $settings['ougc_defaultpoststyle_forums']),
            array('multiple' => true, 'size' => 5)
        );
    }
}

// Hijack the UCP menu
function ougc_defaultpoststyle_menu()
{
    global $PL, $mybb;
    $PL or require_once PLUGINLIBRARY;

    if ($PL->is_member($mybb->settings['ougc_defaultpoststyle_groups'])) {
        global $lang, $templates, $usercpnav;
        ougc_defaultpoststyle_lang_load();

        $menu = eval($templates->render('ougcdefaultpoststyle_menu'));
        $usercpnav = str_replace('<!--OUGC_DEFAULTPOSTSTYLE-->', $menu, $usercpnav);
    }
}

// UCP Page
function ougc_defaultpoststyle_usercp()
{
    global $mybb, $plugins;

    if ($mybb->input['action'] != 'defaultpoststyle') {
        return;
    }

    global $PL;
    $PL or require_once PLUGINLIBRARY;

    // Check group permissions
    if (!$PL->is_member($mybb->settings['ougc_defaultpoststyle_groups'])) {
        error_no_permission();
    }

    global $templates, $lang, $headerinclude, $header, $footer, $usercpnav, $theme;
    ougc_defaultpoststyle_lang_load();

    // Breadcrumb nav
    add_breadcrumb($lang->nav_usercp, $mybb->settings['bburl'] . '/usercp.php');
    add_breadcrumb($lang->ougc_defaultpoststyle_nav);

    $plugins->run_hooks('ougc_defaultpoststyle_usercp_start');

    // Default content for new users / empty content
    if ($mybb->request_method != 'post' && ($mybb->user['ougc_defaultpoststyle'] || !$mybb->input['message'])) {
        $mybb->input['message'] = $mybb->user['ougc_defaultpoststyle'];
        if (!$mybb->input['message']) {
            $mybb->input['message'] = $mybb->settings['ougc_defaultpoststyle_string'];
        }
    }


    // START: Newpoints
    $newpoints = null;
    if (function_exists(
            'newpoints_addpoints'
        ) && $mybb->settings['newpoints_main_enabled'] && $mybb->settings['ougc_defaultpoststyle_newpoints']) {
        $grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
        if (!isset($grouprules['rate'])) {
            $grouprules['rate'] = 1;
        }

        if (!empty($grouprules['rate'])) {
            $newpoints = (float)round(
                $mybb->settings['ougc_defaultpoststyle_newpoints'] * $grouprules['rate'],
                (int)$mybb->settings['newpoints_main_decimal']
            );
        }
    }
    // END: Newpoints

    $errors = array();

    if ($mybb->request_method == 'post') {
        // Just verify if this is a valid post input
        verify_post_check($mybb->input['my_post_key']);

        // Users need to include "ougc_defaultpoststyle_string" value into the message
        if (!my_strpos(
                $mybb->input['message'],
                $mybb->settings['ougc_defaultpoststyle_string']
            ) && $mybb->input['message'] != '') {
            $errors[] = $lang->sprintf(
                $lang->ougc_defaultpoststyle_error_missingstring,
                $mybb->settings['ougc_defaultpoststyle_string']
            );
        }

        // We have a 250 characters limit, even if admin setted longer.
        $limit = (int)$mybb->settings['ougc_defaultpoststyle_limit'];
        $limit = ($limit < 1 || $limit > 250 ? 100 : $limit);

        if (my_strlen($mybb->input['message']) > $limit) {
            $errors[] = $lang->sprintf($lang->ougc_defaultpoststyle_error_limit, my_number_format($limit));
        }

        // START: Newpoints
        if ($newpoints !== null && $mybb->user['newpoints'] < $newpoints) {
            $errors[] = $lang->sprintf(
                $lang->ougc_defaultpoststyle_error_newpoints,
                newpoints_format_points($newpoints)
            );
        }
        // END: Newpoints

        $plugins->run_hooks('ougc_defaultpoststyle_usercp_post', $errors);

        if ($errors) {
            $errors = inline_error($errors);
        } elseif ($mybb->input['preview']) {
            global $parser;

            $preview = $parser->parse_message($mybb->input['message'], array(
                'allow_html' => 0,
                'allow_mycode' => 1,
                'allow_smilies' => 1,
                'allow_imgcode' => 1,
                'allow_videocode' => 1,
                'filter_badwords' => 1
            ));

            eval('$preview = "' . $templates->get('ougcdefaultpoststyle_preview') . '";');
        } else {
            global $db;

            if ($mybb->input['message'] == $mybb->settings['ougc_defaultpoststyle_string']) {
                $mybb->input['message'] = '';
            }

            // START: Newpoints
            if ($newpoints !== null) {
                newpoints_addpoints($mybb->user['uid'], -$newpoints);
            }
            // END: Newpoints

            $db->update_query('users', array(
                'ougc_defaultpoststyle' => $db->escape_string($mybb->input['message'])
            ), 'uid=\'' . $mybb->user['uid'] . '\'');
            redirect($mybb->settings['bburl'] . '/usercp.php', $lang->ougc_defaultpoststyle_redirect);
        }
    }

    // START: Newpoints
    if ($newpoints === null) {
        $newpoints = '';
    } else {
        $lang->ougc_defaultpoststyle_newpoints = $lang->sprintf(
            $lang->ougc_defaultpoststyle_newpoints,
            newpoints_format_points($newpoints)
        );
        eval('$newpoints = "' . $templates->get('ougcdefaultpoststyle_newpoints') . '";');
    }
    // END: Newpoints

    $errors = is_array($errors) ? '' : $errors;
    $preview = !$preview ? '' : $preview;

    $smilieinserter = $mybb->settings['smilieinserter'] ? build_clickable_smilies() : '';
    $codebuttons = $mybb->settings['bbcodeinserter'] ? build_mycode_inserter() : '';

    $plugins->run_hooks('ougc_defaultpoststyle_usercp_end');

    $page = eval($templates->render('ougcdefaultpoststyle'));

    output_page($page);
    exit;
}

// Do the replacements
function ougc_defaultpoststyle_run()
{
    global $PL, $mybb;
    $PL or require_once PLUGINLIBRARY;

    // Check group permissions
    if (!$PL->is_member($mybb->settings['ougc_defaultpoststyle_groups'])) {
        return;
    }

    global $fid;

    // Check forum permissions
    if ($PL->is_member($mybb->settings['ougc_defaultpoststyle_forums'], array('usergroup' => (int)$fid))) {
        return;
    }

    $key = 'message';
    if (!empty($mybb->input['description'])) {
        $key = 'description';
    }

    if (empty($mybb->input[$key])) {
        return;
    }

    $dps = preg_split(
        '#' . $mybb->settings['ougc_defaultpoststyle_string'] . '#',
        $mybb->user['ougc_defaultpoststyle']
    );
    $mybb->input[$key] = $dps[0] . $mybb->input[$key] . $dps[1];
}