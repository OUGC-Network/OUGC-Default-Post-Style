<?php

/***************************************************************************
 *
 *   OUGC Default Post Style plugin (/inc/plugins/ougc_defaultpoststyle.php)
 *   Author: Omar Gonzalez
 *   Copyright: © 2012-2014 Omar Gonzalez
 *
 *   Website: http://omarg.me
 *
 *   Allow users to set a default style for their posts.
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

use function ougc\DefaultPostStyle\Core\addHooks;
use function ougc\DefaultPostStyle\Admin\_info;
use function ougc\DefaultPostStyle\Admin\_activate;
use function ougc\DefaultPostStyle\Admin\_deactivate;
use function ougc\DefaultPostStyle\Admin\_install;
use function ougc\DefaultPostStyle\Admin\_is_installed;
use function ougc\DefaultPostStyle\Admin\_uninstall;

use const ougc\DefaultPostStyle\ROOT;

defined('IN_MYBB') || die('This file cannot be accessed directly.');

// You can uncomment the lines below to avoid storing some settings in the DB
define('ougc\DefaultPostStyle\Core\SETTINGS', [
    //'key' => '',
    'allowedGroupsStyleCode' => '', // disabled because this does nothing at the moment
]);

define('ougc\DefaultPostStyle\Core\DEBUG', true);

define('ougc\DefaultPostStyle\ROOT', constant('MYBB_ROOT') . 'inc/plugins/ougc/DefaultPostStyle');

require_once ROOT . '/core.php';

defined('PLUGINLIBRARY') || define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

// Add our hooks
if (defined('IN_ADMINCP')) {
    require_once ROOT . '/admin.php';
    require_once ROOT . '/hooks/admin.php';

    addHooks('ougc\DefaultPostStyle\Hooks\Admin');
} else {
    require_once ROOT . '/hooks/forum.php';

    addHooks('ougc\DefaultPostStyle\Hooks\Forum');
}

defined('PLUGINLIBRARY') || define('PLUGINLIBRARY', constant('MYBB_ROOT') . 'inc/plugins/pluginlibrary.php');

function ougc_defaultpoststyle_info(): array
{
    return _info();
}

function ougc_defaultpoststyle_activate(): bool
{
    return _activate();
}

function ougc_defaultpoststyle_deactivate(): bool
{
    return _deactivate();
}

function ougc_defaultpoststyle_install(): bool
{
    return _install();
}

function ougc_defaultpoststyle_is_installed(): bool
{
    return _is_installed();
}

function ougc_defaultpoststyle_uninstall(): bool
{
    return _uninstall();
}