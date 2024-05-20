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

namespace ougc\DefaultPostStyle\Admin;

use DirectoryIterator;

use function ougc\DefaultPostStyle\Core\loadLanguage;
use function ougc\DefaultPostStyle\Core\loadPluginLibrary;

use const ougc\DefaultPostStyle\ROOT;

const TABLES_DATA = [
    'ougcDefaultPostStyleTemplates' => [
        'templateID' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'userID' => [
            'type' => 'INT',
            'unsigned' => true
        ],
        'templateName' => [
            'type' => 'VARCHAR',
            'size' => 200,
            'formType' => 'textBox',
        ],
        'templateStyle' => [
            'type' => 'TEXT',
            'formType' => 'textArea',
            'null' => true,
        ],
        'templateContents' => [
            'type' => 'TEXT',
            'formType' => 'textArea',
            'null' => true,
        ],
        'isEnabled' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'isDefault' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'updateStamp' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ]
    ],
];

const FIELDS_DATA = [
    'users' => [
        //'ougc_defaultpoststyle' => "varchar(255) NOT NULL DEFAULT ''", // todo, drop later
        'ougcDefaultPostStyleDefaultTemplateID' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT 0'
    ],
    'posts' => [
        'ougcDefaultPostStyleTemplateID' => 'int UNSIGNED NOT NULL DEFAULT 0'
    ]
];

function _info(): array
{
    global $lang;

    loadLanguage();

    return [
        'name' => 'OUGC Default Post Style (Templates)',
        'description' => $lang->ougcDefaultPostStyleDescription,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.1',
        'versioncode' => 1801,
        'compatibility' => '18*',
        'codename' => 'ougcDefaultPostStyle',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

function _activate(): bool
{
    global $PL, $lang, $cache;

    loadPluginLibrary();

    // Add settings group
    $settingsContents = file_get_contents(ROOT . '/settings.json');

    $settingsData = json_decode($settingsContents, true);

    foreach ($settingsData as $settingKey => &$settingData) {
        if (empty($lang->{"setting_ougc_defaultpoststyle_{$settingKey}"})) {
            continue;
        }

        if (in_array($settingData['optionscode'], ['select', 'checkbox', 'radio'])) {
            foreach ($settingData['options'] as $optionKey) {
                $settingData['optionscode'] .= "\n{$optionKey}={$lang->{"setting_ougc_defaultpoststyle_{$settingKey}_{$optionKey}"}}";
            }
        }

        $settingData['title'] = $lang->{"setting_ougc_defaultpoststyle_{$settingKey}"};
        $settingData['description'] = $lang->{"setting_ougc_defaultpoststyle_{$settingKey}_desc"};
    }

    $PL->settings(
        'ougc_defaultpoststyle',
        $lang->setting_group_ougc_defaultpoststyle,
        $lang->setting_group_ougc_defaultpoststyle_desc,
        $settingsData
    );

    $templatesDirIterator = new DirectoryIterator(ROOT . '/templates');

    $templates = [];

    foreach ($templatesDirIterator as $template) {
        if (!$template->isFile()) {
            continue;
        }

        $pathName = $template->getPathname();

        $pathInfo = pathinfo($pathName);

        if ($pathInfo['extension'] === 'html') {
            $templates[$pathInfo['filename']] = file_get_contents($pathName);
        }
    }

    if ($templates) {
        $PL->templates('ougcdefaultpoststyle', 'OUGC Default Post Style', $templates);
    }

    // Insert/update version into cache
    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    $_info = _info();

    if (!isset($plugins['defaultpoststyle'])) {
        $plugins['defaultpoststyle'] = $_info['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    dbVerifyTables();

    dbVerifyColumns();

    $plugins['defaultpoststyle'] = $_info['versioncode'];

    $cache->update('ougc_plugins', $plugins);

    return true;
}

function _deactivate(): bool
{
    return true;
}

function _install(): bool
{
    dbVerifyTables();

    dbVerifyColumns();

    return true;
}

function _is_installed(): bool
{
    static $isInstalled = null;

    if ($isInstalled === null) {
        global $db;

        $isInstalled = false;

        foreach (dbTables() as $tableName => $tableData) {
            $isInstalled = (bool)$db->table_exists($tableName) ?? false;

            break;
        }
    }

    return $isInstalled;
}

function _uninstall(): bool
{
    global $db, $PL, $cache;

    loadPluginLibrary();

    foreach (TABLES_DATA as $tableName => $tableData) {
        if ($db->table_exists($tableName)) {
            $db->drop_table($tableName);
        }
    }

    foreach (FIELDS_DATA as $tableName => $tableColumns) {
        if ($db->table_exists($tableName)) {
            foreach ($tableColumns as $field => $definition) {
                if ($db->field_exists($field, $tableName)) {
                    $db->drop_column($tableName, $field);
                }
            }
        }
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
        $cache->delete('ougc_plugins');
    }

    return true;
}

function dbTables(): array
{
    $tablesData = [];

    foreach (TABLES_DATA as $tableName => $fieldsData) {
        foreach ($fieldsData as $fieldName => $fieldData) {
            $fieldDefinition = '';

            if (!isset($fieldData['type'])) {
                continue;
            }

            $fieldDefinition .= $fieldData['type'];

            if (isset($fieldData['size'])) {
                $fieldDefinition .= "({$fieldData['size']})";
            }

            if (isset($fieldData['unsigned'])) {
                if ($fieldData['unsigned'] === true) {
                    $fieldDefinition .= ' UNSIGNED';
                } else {
                    $fieldDefinition .= ' SIGNED';
                }
            }

            if (!isset($fieldData['null'])) {
                $fieldDefinition .= ' NOT';
            }

            $fieldDefinition .= ' NULL';

            if (isset($fieldData['auto_increment'])) {
                $fieldDefinition .= ' AUTO_INCREMENT';
            }

            if (isset($fieldData['default'])) {
                $fieldDefinition .= " DEFAULT '{$fieldData['default']}'";
            }

            $tablesData[$tableName][$fieldName] = $fieldDefinition;
        }

        foreach ($fieldsData as $fieldName => $fieldData) {
            if (isset($fieldData['primary_key'])) {
                $tablesData[$tableName]['primary_key'] = $fieldName;
            }
            if ($fieldName === 'unique_key') {
                $tablesData[$tableName]['unique_key'] = $fieldData;
            }
        }
    }

    return $tablesData;
}

function dbVerifyTables(): bool
{
    global $db;

    $collation = $db->build_create_table_collation();

    $tablePrefix = $db->table_prefix;

    foreach (dbTables() as $tableName => $tableData) {
        if ($db->table_exists($tableName)) {
            foreach ($tableData as $field => $definition) {
                if ($field == 'primary_key' || $field == 'unique_key') {
                    continue;
                }

                if ($db->field_exists($field, $tableName)) {
                    $db->modify_column($tableName, "`{$field}`", $definition);
                } else {
                    $db->add_column($tableName, $field, $definition);
                }
            }
        } else {
            $query = "CREATE TABLE IF NOT EXISTS `{$tablePrefix}{$tableName}` (";

            foreach ($tableData as $field => $definition) {
                if ($field == 'primary_key') {
                    $query .= "PRIMARY KEY (`{$definition}`)";
                } elseif ($field != 'unique_key') {
                    $query .= "`{$field}` {$definition},";
                }
            }

            $query .= ") ENGINE=MyISAM{$collation};";

            $db->write_query($query);
        }
    }

    dbVerifyIndexes();

    return true;
}

function dbVerifyIndexes(): bool
{
    global $db;

    $tablePrefix = $db->table_prefix;

    foreach (dbTables() as $tableName => $tableData) {
        if (!$db->table_exists($tableName)) {
            continue;
        }

        if (isset($tableData['unique_key'])) {
            foreach ($tableData['unique_key'] as $keyName => $keyValue) {
                if ($db->index_exists($tableName, $keyName)) {
                    continue;
                }

                $db->write_query("ALTER TABLE {$tablePrefix}{$tableName} ADD UNIQUE KEY {$keyName} ({$keyValue})");
            }
        }
    }

    return true;
}

function dbVerifyColumns(): bool
{
    global $db;

    foreach (FIELDS_DATA as $tableName => $fieldsData) {
        foreach ($fieldsData as $fieldName => $fieldDefinition) {
            if ($db->field_exists($fieldName, $tableName)) {
                $db->modify_column($tableName, "`{$fieldName}`", $fieldDefinition);
            } else {
                $db->add_column($tableName, $fieldName, $fieldDefinition);
            }
        }
    }

    return true;
}