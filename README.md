<p align="center">
    <a href="" rel="noopener">
        <img width="700" height="400" src="https://github.com/Sama34/OUGC-Default-Post-Style/assets/1786584/cf87392b-96ea-4993-8f48-5a5eb556b47b" alt="Project logo">
    </a>
</p>

<h3 align="center">OUGC Default Post Style</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/OUGC-Default-Post-Style.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-Network/OUGC-Default-Post-Style.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Allow users to set a default style for their posts.
    <br> 
</p>

## 📜 Table of Contents <a name = "table_of_contents"></a>

- [About](#about)
- [Getting Started](#getting_started)
    - [Dependencies](#dependencies)
    - [File Structure](#file_structure)
    - [Install](#install)
    - [Update](#update)
    - [Template Modifications](#template_modifications)
- [Settings](#settings)
- [Usage](#usage)
- [Built Using](#built_using)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🚀 About <a name = "about"></a>

Default Post Style lets users easily set a default style for their posts – making them stand out from the crowd. Plus,
with parsing options and character limits, you have full control over how your posts look. Additionally, you can tweak
settings like ignored forums and allowed groups to make sure post templates shines exactly where you want it to. Want to
unlock even more customization? You can use Newpoints points to access this feature. With this plugin it's easy to add
your own personal touch and make your mark on the forum scene!

[Go up to Table of Contents](#table_of_contents)

## 📍 Getting Started <a name = "getting_started"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

- [MyBB](https://mybb.com/) >= 1.8
- PHP >= 7.0
- [PluginLibrary for MyBB](https://github.com/frostschutz/MyBB-PluginLibrary) >= 13
- [Newpoints](https://community.mybb.com/mods.php?action=view&pid=94) >= 2.0.4 (Optional)

### File structure <a name = "file_structure"></a>

  ```
   .
   ├── inc
   │ ├── languages
   │ │ ├── english
   │ │ │ ├── admin
   │ │ │ │ ├── ougc_defaultpoststyle.lang.php
   │ │ │ ├── ougc_defaultpoststyle.lang.php
   │ ├── plugins
   │ │ ├── ougc
   │ │ │ ├── DefaultPostStyle
   │ │ │ │ ├── hooks
   │ │ │ │ │ ├── admin.php
   │ │ │ │ │ ├── forum.php
   │ │ │ │ ├── templates
   │ │ │ │ │ ├── .html
   │ │ │ │ │ ├── form.html
   │ │ │ │ │ ├── formPreview.html
   │ │ │ │ │ ├── formRowNewpoints.html
   │ │ │ │ │ ├── formRowStyleCode.html
   │ │ │ │ │ ├── menu.html
   │ │ │ │ │ ├── moderatorNav.html
   │ │ │ │ │ ├── moderatorUserForm.html
   │ │ │ │ │ ├── moderatorUserMessage.html
   │ │ │ │ │ ├── newReply.html
   │ │ │ │ │ ├── rowsEmpty.html
   │ │ │ │ │ ├── rowsItem.html
   │ │ │ │ │ ├── select.html
   │ │ │ │ │ ├── selectButton.html
   │ │ │ │ │ ├── selectOption.html
   │ │ │ │ │ ├── userTable.html
   │ │ │ │ ├── admin.php
   │ │ │ │ ├── core.php
   │ │ │ │ ├── settings.json
   │ │ ├── ougc_defaultpoststyle.php
   ```

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package from the [MyBB Extend](https://community.mybb.com/mods.php?action=view&pid=399) site or
   from
   the [repository releases](https://github.com/OUGC-Network/OUGC-Default-Post-Style/releases/latest).
2. Upload the contents of the _Upload_ folder to your MyBB root directory.
3. Browse to _Configuration » Plugins_ and install this plugin by clicking _Install & Activate_.

### Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.

### Template Modifications <a name = "template_modifications"></a>

To display the form input row it is required that you edit the following templates for each of your themes.

1. Open the `newthread` template for editing.
2. Add `{$ougcDefaultPostStyleSelectRow}` after `{postoptions}`.
3. Save the template.
4. Open the `newreply` template for editing.
5. Add `{$ougcDefaultPostStyleSelectRow}` after `{postoptions}`.
6. Save the template.
7. Open the `editpost` template for editing.
8. Add `{$ougcDefaultPostStyleSelectRow}` after `{postoptions}`.
9. Save the template.
10. Open the `modcp_nav_users` template for editing.
11. Add `<!--OUGC_DEFAULTPOSTSTYLE-->` after `{$nav_ipsearch}`.
12. Save the template.
13. Open the `usercp_nav_misc` template for editing.
14. Add `<!--OUGC_DEFAULTPOSTSTYLE-->` after `{$attachmentop}`.
15. Save the template.

[Go up to Table of Contents](#table_of_contents)

## 🛠 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Global Settings

- **Ignored Forums** `select`
    - _Select which forums are ignored for using custom post templates._
- **Allowed Groups** `select`
    - _Select which groups are allowed to create custom post templates._
- **Moderator Groups** `select`
    - _Select which groups are allowed to moderate user custom post templates._
- **Parser Options** `checkBox`
    - _Select the parsing options for user custom post templates._
- **Character Limit** `numeric`
    - _Maximum number of characters users may use for their custom post templates._
- **Replacement String** `text` Default: `{MESSAGE}`
    - _String to be replaced within the post style to define the post's content._
- **Newpoints Payment** `float` Default: `0`
    - _Select the amount of points users HAVE to pay each time they create or update their custom post templates._
- **Fallback to Default Template** `yesNo` Default: `no`
    - _If you enable this, the user default template will be used for posts when no post template is assigned to them._
- **Enable Insert Button** `yesNo` Default: `no`
    - _Show an insert button along the select form to allow insertion of templates directly into messages._
- **Page Action** `text` Default: `defaultpoststyle`
    - _Select the User Control Panel page action to use for this plugin._

[Go up to Table of Contents](#table_of_contents)

## 📖 Usage <a name="usage"></a>

This plugin has no additional configurations; after activating make sure to modify the global settings in order to get
this plugin working.

[Go up to Table of Contents](#table_of_contents)

## ⛏ Built Using <a name = "built_using"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#table_of_contents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](https://github.com/OUGC-Network/OUGC-Default-Post-Style/contributors) who
participated in this project.

[Go up to Table of Contents](#table_of_contents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#table_of_contents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the
official [MyBB Community](https://community.mybb.com/thread-221815.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#table_of_contents)yleSelectRow}