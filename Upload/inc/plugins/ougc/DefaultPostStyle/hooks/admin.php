<?php

/***************************************************************************
 *
 *    OUGC Default Post Style plugin (/inc/plugins/ougc/DefaultPostStyle/admin.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012-2014 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow users to set a default style for their posts.
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

declare(strict_types=1);

namespace ougc\DefaultPostStyle\Hooks\Admin;

use MyBB;

use function ougc\DefaultPostStyle\Admin\_db_columns;
use function ougc\DefaultPostStyle\Core\loadLanguage;
use function ougc\DefaultPostStyle\Admin\_activate;

function admin_config_plugins_deactivate(): bool
{
    global $mybb, $page;

    if (
        $mybb->get_input('action') != 'deactivate' ||
        $mybb->get_input('plugin') != 'ougc_defaultpoststyle' ||
        !$mybb->get_input('uninstall', MyBB::INPUT_INT)
    ) {
        return false;
    }

    if ($mybb->request_method != 'post') {
        $page->output_confirm_action(
            'index.php?module=config-plugins&amp;action=deactivate&amp;uninstall=1&amp;plugin=ougc_defaultpoststyle'
        );
    }

    if ($mybb->get_input('no')) {
        admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function admin_config_settings_start()
{
    loadLanguage();
}

function admin_style_templates_set()
{
    loadLanguage();
}

function admin_config_settings_change()
{
    global $db, $mybb;

    $query = $db->simple_select('settinggroups', 'name', "gid='{$mybb->get_input('gid', MyBB::INPUT_INT)}'");

    !($db->fetch_field($query, 'name') == 'ougc_defaultpoststyle') || loadLanguage();
}